<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2020, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.4.0
 * ---------------------------------------------------------------------------- */

/**
 * Class Availability
 *
 * Handles the availability generation of providers, based on their working plan and their schedule.
 */
class Availability {
    /**
     * @var EA_Controller
     */
    protected $CI;

    const FREE_SLOT = true;
    const BUSY_SLOT = false;

    /**
     * Availability constructor.
     */
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('providers_model');
        $this->CI->load->model('secretaries_model');
        $this->CI->load->model('secretaries_model');
        $this->CI->load->model('admins_model');
        $this->CI->load->model('appointments_model');
        $this->CI->load->model('settings_model');
        $this->CI->load->library('ics_file');
    }

    /**
     * Get the available hours of a provider.
     *
     * @param string $date Selected date (Y-m-d).
     * @param array $service Service record.
     * @param array $provider Provider record.
     * @param int|null $exclude_appointment_id Exclude an appointment from the availability generation.
     *
     * @return array
     *
     * @throws Exception
     */
    public function get_available_hours($date, $service, $provider, $exclude_appointment_id = NULL)
    {
        $available_periods = $this->my_get_available_periods($date, $provider, $service, $exclude_appointment_id);

        $available_hours = $this->generate_available_hours($date, $service, $available_periods);

        return $this->consider_book_advance_timeout($date, $available_hours, $provider);
    }

    /**
     * Get an array containing the free time periods (start - end) of a selected date.
     *
     * This method is very important because there are many cases where the system needs to know when a provider is
     * available for an appointment. This method will return an array that belongs to the selected date and contains
     * values that have the start and the end time of an available time period.
     *
     * @param string $date Select date string.
     * @param array $provider Provider record.
     * @param array $askedService
     * @param mixed $exclude_appointment_id Exclude an appointment from the availability generation.
     *
     * @return array Returns an array with the available time periods of the provider.
     *
     * @throws Exception
     */

    protected function my_get_available_periods(String $date, Array $provider, Array $askedService = null, String $exclude_appointment_id = null) : Array
    {
        // Get the service, provider's working plan and provider appointments.
        $working_plan = json_decode($provider['settings']['working_plan'], true);

        // Get the provider's working plan exceptions.
        $working_plan_exceptions_json = $provider['settings']['working_plan_exceptions'];
        $working_plan_exceptions = $working_plan_exceptions_json ? json_decode($working_plan_exceptions_json, true) : null;

        $conditions['id_users_provider'] = $provider['id'];

        // Sometimes it might be necessary to exclude an appointment from the calculation (e.g. when editing an existing appointment).
        if ($exclude_appointment_id) $conditions['id !='] = $exclude_appointment_id;

        // Read all the appointments.
        $appointments = $this->CI->appointments_model->get_batch($conditions) ?? array();

        // Read all categories.
        $categories = $this->CI->services_model->get_all_categories() ?? array();

        // Read all services.
        $services = $this->CI->services_model->get_available_services() ?? array();

        // A day consists of 24*4 quarter hours completely free by default.
        for ($i = 0; $i < 24 * 4; $i++) { $day[$i] = self::BUSY_SLOT; $servicesDay[$i] = 0; $categoriesDay[$i] = 0; }

        // Working plan corresponding to the selected date.
        $date_working_plan = $working_plan[strtolower(date('l', strtotime($date)))] ?? NULL;

        // Search if the $date is an custom availability period added outside the normal working plan.
        if (isset($working_plan_exceptions[$date])) $date_working_plan = $working_plan_exceptions[$date];

        // If no working plane is defined for the selected date, return an empty array.
        if (!isset($date_working_plan)) return array();

        // Add the working slot, depending weither it is normal or custom availability period.
        $this->putSlot($date_working_plan['start'], $date_working_plan['end'], $day, self::FREE_SLOT);

        // Search the category of the possible supplied service.
        $askedCat = $this->searchCategory($askedService, $categories);

        // Subtract all the breaks from the working day.
        if (isset($date_working_plan['breaks']))
            foreach ($date_working_plan['breaks'] as $break)
                $this->putSlot($break['start'], $break['end'], $day, self::BUSY_SLOT);

        // Subtract all the private specialized slots from the working day.
        if (isset($date_working_plan['specializeds']))
        {
            foreach ($date_working_plan['specializeds'] as $specialized)
            {
                // If the category is private, subtract the slot by marking it busy.
                if ($this->isPrivate($specialized['category'], $categories))
                    $this->putSlot($specialized['start'], $specialized['end'], $day, self::BUSY_SLOT);

                // Categorize this period of the day.
                $this->putCat($specialized['start'], $specialized['end'], $categoriesDay, $specialized['category']);
            }
        }

        // Subtract all the appointments from the working day.
        foreach ($appointments as $appointment)
        {
            $start = $appointment['start_datetime'];
            $end = $appointment['end_datetime'];
            $startDay = date_create_from_format('Y-m-d', substr($appointment['start_datetime'], 0, 10));
            $curDay = date_create_from_format('Y-m-d', substr($date, 0, 10));

            // If this is not for the current day, skip-it.
            if (date_format($startDay, 'dmo') != date_format($curDay, 'dmo')) continue;

            // Search the service this appointment belongs to.
            for ($ser = 0; $ser < count($services); $ser++) if ($services[$ser]['id'] == $appointment['id_services']) break;

            // If no service was found, ignore the appointment.
            if ($ser == count($services)) continue;

            // Search the category of the service this appointment belongs to and ignore-it if private
            if ( ($cat = $this->searchCategory($services[$ser], $categories)) && ($this->isPrivate($cat, $categories)) ) continue;

            // Handle the appointment according to his attendants customers.
            $this->putAppointment($start, $end, $day, $servicesDay, $services[$ser]['attendants_number']);
        }

        return $this->searchSlots($day, $categoriesDay, $askedCat, self::FREE_SLOT);
    }

    /**
     * Mark a period of time as available or not depending of the $state parameter.
     *
     * @param String $startHour
     * @param String $endHour
     * @param Array  $day
     * @param Bool   $state
     *
     * @return void
     */

    private function putSlot(string $startHour, string $endHour, Array &$day, Bool $state)
    {
        $startDate = new DateTime($startHour);
        $endDate = new DateTime($endHour);

        $start = (date_format($startDate, 'H') * 4 + date_format($startDate, 'i') / 15) *1;
        $end = (date_format($endDate, 'H') * 4 + date_format($endDate, 'i') / 15) *1;

        for ($i = $start; $i < $end; $i++) $day[$i] = $state;
    }

    /**
     * @param string $startHour
     * @param string $endHour
     * @param array  $categoriesDay
     * @param int    $categoryId
     *
     * @return void
     * @throws Exception
     */

    private function putCat(string $startHour, string $endHour, Array &$categoriesDay, int $categoryId)
    {
        $startDate = new DateTime($startHour);
        $endDate = new DateTime($endHour);

        $start = (date_format($startDate, 'H') * 4 + date_format($startDate, 'i') / 15) *1;
        $end = (date_format($endDate, 'H') * 4 + date_format($endDate, 'i') / 15) *1;

        for ($i = $start; $i < $end; $i++) $categoriesDay[$i] = $categoryId;
    }

    /**
     * @param $day
     * @param $categoriesDay
     * @param $askedCat
     * @param $state
     *
     * @return array
     * @throws Exception
     */

    private function searchSlots(&$day, &$categoriesDay, $askedCat, $state): array
    {
        $slots = array();

        // Scan the day supplied in parameters.
        for ($i = 0, $start = NULL, $end = NULL, $put = FALSE; $i < 24 * 4; $i++)
        {
            // if the beginning of a desired slot is found.
            if (($day[$i] == $state) && !(($askedCat) && ($categoriesDay[$i] != $askedCat)))
            {
                if ($start == NULL) $start = $i;
                $end = $i + 1;
                $put = ($i == 24 * 4 - 1);
            }
            else
            {
                $put = ($start != NULL);
            }

            // If a slot is entirely found.
            if ($put)
            {
                // Converts number of quarter hours to hours and minutes.
                $startStr = strval(intval($start * 15 / 60)) . ':' . strval($start * 15 % 60);
                $endStr = strval(intval($end * 15 / 60)) . ':' . strval($end * 15 % 60);

                // Create a slot.
                $slots[] = array('start' => (new DateTime($startStr))->format('H:i'), 'end' => (new DateTime($endStr))->format('H:i'));

                // Reset the variables for the next slot.
                $start = NULL;
                $end = NULL;
                $put = FALSE;
            }
        }

        return $slots;
    }

    /**
     * Détermine if the given category is private or not.
     *
     * @param int   $category
     * @param array $categories
     *
     * @return bool
     */

    private function isPrivate(int $category, array $categories): bool
    {
        foreach ($categories as $cat) if ($cat['id'] == $category) return $cat['is_private'];

        return false;
    }

    /**
     * @param array $service
     * @param array $categories
     *
     * @return int
     */

    private function searchCategory(array $service, array $categories): int
    {
        // If no service is supplied or has no category, return 0.
        if ( (!isset($service)) || (!$service['id_service_categories']) ) return 0;

        // Search the category of the service.
        foreach ($categories as $cat) if ($cat['id'] == $service['id_service_categories']) return $cat['id'];

        // If no category was found, return 0.
        return 0;
    }

    /**
     * @param string $startHour
     * @param string $endHour
     * @param array  $day
     * @param array  $servicesDay
     * @param int    $attendants
     *
     * @return void
     * @throws Exception
     */

    private function putAppointment(string $startHour, string $endHour, Array &$day, Array &$servicesDay, int $attendants)
    {
        $startDate = new DateTime($startHour);
        $endDate = new DateTime($endHour);

        $start = (date_format($startDate, 'H') * 4 + date_format($startDate, 'i') / 15) * 1;
        $end = (date_format($endDate, 'H') * 4 + date_format($endDate, 'i') / 15) * 1;

        for ($i = $start; $i < $end; $i++)
        {
            if ($day[$i] == self::BUSY_SLOT) continue;

            if (!$servicesDay[$i]) $servicesDay[$i] = $attendants;

            $servicesDay[$i]--;

            $day[$i] = $servicesDay[$i] ? self::FREE_SLOT : self::BUSY_SLOT;
        }
    }

    /**
     * Calculate the available appointment hours.
     *
     * Calculate the available appointment hours for the given date. The empty spaces
     * are broken down to 15 min and if the service fit in each quarter then a new
     * available hour is added to the "$available_hours" array.
     *
     * @param string $date Selected date (Y-m-d).
     * @param array $service Service record.
     * @param array $empty_periods Empty periods as generated by the "get_provider_available_time_periods"
     * method.
     *
     * @return array Returns an array with the available hours for the appointment.
     *
     * @throws Exception
     */

    protected function generate_available_hours($date, $service, $empty_periods)
    {
        $available_hours = [];

        foreach ($empty_periods as $period)
        {
            $start_hour = new DateTime($date . ' ' . $period['start']);
            $end_hour = new DateTime($date . ' ' . $period['end']);
            $interval = $service['availabilities_type'] === AVAILABILITIES_TYPE_FIXED ? (int)$service['duration'] : 15;

            $current_hour = $start_hour;
            $diff = $current_hour->diff($end_hour);

            while (($diff->h * 60 + $diff->i) >= (int)$service['duration'] && $diff->invert === 0)
            {
                $available_hours[] = $current_hour->format('H:i');
                $current_hour->add(new DateInterval('PT' . $interval . 'M'));
                $diff = $current_hour->diff($end_hour);
            }
        }

        return $available_hours;
    }

    /**
     * Consider the book advance timeout and remove available hours that have passed the threshold.
     *
     * If the selected date is today, remove past hours. It is important  include the timeout before booking
     * that is set in the back-office the system. Normally we might want the customer to book an appointment
     * that is at least half or one hour from now. The setting is stored in minutes.
     *
     * @param string $selected_date The selected date.
     * @param array $available_hours Already generated available hours.
     * @param array $provider Provider information.
     *
     * @return array Returns the updated available hours.
     *
     * @throws Exception
     */

    protected function consider_book_advance_timeout($selected_date, $available_hours, $provider)
    {
        $provider_timezone = new DateTimeZone($provider['timezone']);

        $book_advance_timeout = $this->CI->settings_model->get_setting('book_advance_timeout');

        $threshold = new DateTime('+' . $book_advance_timeout . ' minutes', $provider_timezone);

        foreach ($available_hours as $index => $value)
        {
            $available_hour = new DateTime($selected_date . ' ' . $value, $provider_timezone);

            if ($available_hour->getTimestamp() <= $threshold->getTimestamp())
            {
                unset($available_hours[$index]);
            }
        }

        $available_hours = array_values($available_hours);
        sort($available_hours, SORT_STRING);
        return array_values($available_hours);
    }
}
