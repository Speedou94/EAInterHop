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
        $available_periods = $this->get_available_periods($date, $provider, $exclude_appointment_id);

        $available_hours = $this->generate_available_hours($date, $service, $available_periods);

        /*if ($service['attendants_number'] > 1)
        {
            $available_hours = $this->consider_multiple_attendants($date, $service, $provider, $exclude_appointment_id);
        }*/

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
     * @param array $askedServices
     * @param mixed $exclude_appointment_id Exclude an appointment from the availability generation.
     *
     * @return array Returns an array with the available time periods of the provider.
     *
     * @throws Exception
     */

    protected function my_get_available_periods(String $date, Array $provider, Array $askedServices = null, String $exclude_appointment_id = null) : Array
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
        for ($i = 0; $i < 24 * 4; $i++) { $day[$i] = self::BUSY_SLOT; $servicesDay[$i] = 0; }

        // Working plan corresponding to the selected date.
        $date_working_plan = $working_plan[strtolower(date('l', strtotime($date)))] ?? NULL;

        // Search if the $date is an custom availability period added outside the normal working plan.
        if (isset($working_plan_exceptions[$date])) $date_working_plan = $working_plan_exceptions[$date];

        // If no working plane is defined for the selected date, return an empty array.
        if (!isset($date_working_plan)) return array();

        // Add the working slot, depending weither it is normal or custom availability period.
        $this->putSlot($date_working_plan['start'], $date_working_plan['end'], $day, self::FREE_SLOT);

        // Subtract all the breaks from the working day.
        if (isset($date_working_plan['breaks']))
            foreach ($date_working_plan['breaks'] as $break)
                $this->putSlot($break['start'], $break['end'], $day, self::BUSY_SLOT);

        // Subtract all the private specialized slots from the working day.
        if (isset($date_working_plan['specializeds']))
            foreach ($date_working_plan['specializeds'] as $specialized)
                if ($this->isPrivate($specialized['category'], $categories))
                    $this->putSlot($specialized['start'], $specialized['end'], $day, self::BUSY_SLOT);

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

            // Search the category of the service.
            for ($cat = 0; $cat < count($categories); $cat++) if ($categories[$cat]['id'] == $services[$ser]['id_service_categories']) break;

            // If the category of the appointment service is private, ignore the appointment.
            if ( ($cat != count($categories)) && ($categories[$cat]['is_private']) ) continue;

            // Handle the appointment according to his attendants customers.
            $this->putAppointment($start, $end, $day, $servicesDay, $services[$ser]['attendants_number']);
        }

        return $this->searchSlots($day, self::FREE_SLOT);
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
     * Search for the desired slots in the working day.
     *
     * @param $day
     * @param $state
     *
     * @return array
     * @throws Exception
     */

    private function searchSlots($day, $state): array
    {
        $slots = array();

        // Scan the day supplied in parameters.
        for ($i = 0, $start = NULL, $end = NULL, $put = FALSE; $i < 24 * 4; $i++)
        {
            // if the beginning or a desired slot is found.
            if ($day[$i] == $state)
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
            if (!$servicesDay[$i]) $servicesDay[$i] = $attendants;

            $servicesDay[$i]--;

            $day[$i] = $servicesDay[$i] ? self::FREE_SLOT : self::BUSY_SLOT;
        }
    }










    protected function get_available_periods($date, $provider, $exclude_appointment_id = NULL)
    {
        $periods = $this->my_get_available_periods($date, $provider, null, $exclude_appointment_id);

        return $periods;

        // Get the service, provider's working plan and provider appointments.
        $working_plan = json_decode($provider['settings']['working_plan'], TRUE);

        // Get the provider's working plan exceptions.
        $working_plan_exceptions_json = $provider['settings']['working_plan_exceptions'];
        
        $working_plan_exceptions = $working_plan_exceptions_json ? json_decode($provider['settings']['working_plan_exceptions'], TRUE) : NULL;

        $conditions = [
            'id_users_provider' => $provider['id'],
        ];

        // Sometimes it might be necessary to exclude an appointment from the calculation (e.g. when editing an
        // existing appointment).
        if ($exclude_appointment_id)
        {
            $conditions['id !='] = $exclude_appointment_id;
        }

        $appointments = $this->CI->appointments_model->get_batch($conditions);

        // Find the empty spaces on the plan. The first split between the plan is due to a break (if any). After that
        // every reserved appointment is considered to be a taken space in the plan.
        $working_day = strtolower(date('l', strtotime($date)));

        $date_working_plan = $working_plan[$working_day] ?? NULL;

        // Search if the $date is an custom availability period added outside the normal working plan.
        if (isset($working_plan_exceptions[$date]))
        {
            $date_working_plan = $working_plan_exceptions[$date];
        }

        $periods = [];

        if (isset($date_working_plan['breaks']))
        {
            $periods[] = [
                'start' => $date_working_plan['start'],
                'end' => $date_working_plan['end']
            ];

            $day_start = new DateTime($date_working_plan['start']);
            $day_end = new DateTime($date_working_plan['end']);

            // Split the working plan to available time periods that do not contain the breaks in them.
            foreach ($date_working_plan['breaks'] as $index => $break)
            {
                $break_start = new DateTime($break['start']);
                $break_end = new DateTime($break['end']);

                if ($break_start < $day_start)
                {
                    $break_start = $day_start;
                }

                if ($break_end > $day_end)
                {
                    $break_end = $day_end;
                }

                if ($break_start >= $break_end)
                {
                    continue;
                }

                foreach ($periods as $key => $period)
                {
                    $period_start = new DateTime($period['start']);
                    $period_end = new DateTime($period['end']);

                    $remove_current_period = FALSE;

                    if ($break_start > $period_start && $break_start < $period_end && $break_end > $period_start)
                    {
                        $periods[] = [
                            'start' => $period_start->format('H:i'),
                            'end' => $break_start->format('H:i')
                        ];

                        $remove_current_period = TRUE;
                    }

                    if ($break_start < $period_end && $break_end > $period_start && $break_end < $period_end)
                    {
                        $periods[] = [
                            'start' => $break_end->format('H:i'),
                            'end' => $period_end->format('H:i')
                        ];

                        $remove_current_period = TRUE;
                    }

                    if ($break_start == $period_start && $break_end == $period_end)
                    {
                        $remove_current_period = TRUE;
                    }

                    if ($remove_current_period)
                    {
                        unset($periods[$key]);
                    }
                }
            }
        }

        // Break the empty periods with the reserved appointments.
        foreach ($appointments as $appointment)
        {
            foreach ($periods as $index => &$period)
            {
                $appointment_start = new DateTime($appointment['start_datetime']);
                $appointment_end = new DateTime($appointment['end_datetime']);

                if ($appointment_start >= $appointment_end)
                {
                    continue;
                }

                $period_start = new DateTime($date . ' ' . $period['start']);
                $period_end = new DateTime($date . ' ' . $period['end']);

                if ($appointment_start <= $period_start && $appointment_end <= $period_end && $appointment_end <= $period_start)
                {
                    // The appointment does not belong in this time period, so we  will not change anything.
                    continue;
                }
                else
                {
                    if ($appointment_start <= $period_start && $appointment_end <= $period_end && $appointment_end >= $period_start)
                    {
                        // The appointment starts before the period and finishes somewhere inside. We will need to break
                        // this period and leave the available part.
                        $period['start'] = $appointment_end->format('H:i');
                    }
                    else
                    {
                        if ($appointment_start >= $period_start && $appointment_end < $period_end)
                        {
                            // The appointment is inside the time period, so we will split the period into two new
                            // others.
                            unset($periods[$index]);

                            $periods[] = [
                                'start' => $period_start->format('H:i'),
                                'end' => $appointment_start->format('H:i')
                            ];

                            $periods[] = [
                                'start' => $appointment_end->format('H:i'),
                                'end' => $period_end->format('H:i')
                            ];
                        }
                        else if ($appointment_start == $period_start && $appointment_end == $period_end)
                        {
                            unset($periods[$index]); // The whole period is blocked so remove it from the available periods array.
                        }
                        else
                        {
                            if ($appointment_start >= $period_start && $appointment_end >= $period_start && $appointment_start <= $period_end)
                            {
                                // The appointment starts in the period and finishes out of it. We will need to remove
                                // the time that is taken from the appointment.
                                $period['end'] = $appointment_start->format('H:i');
                            }
                            else
                            {
                                if ($appointment_start >= $period_start && $appointment_end >= $period_end && $appointment_start >= $period_end)
                                {
                                    // The appointment does not belong in the period so do not change anything.
                                    continue;
                                }
                                else
                                {
                                    if ($appointment_start <= $period_start && $appointment_end >= $period_end && $appointment_start <= $period_end)
                                    {
                                        // The appointment is bigger than the period, so this period needs to be removed.
                                        unset($periods[$index]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return array_values($periods);
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
     * Get multiple attendants hours.
     *
     * This method will add the additional appointment hours whenever a service accepts multiple attendants.
     *
     * @param string $date Selected date (Y-m-d).
     * @param array $service Service record.
     * @param array $provider Provider record.
     * @param int|null $exclude_appointment_id Exclude an appointment from the availability generation.
     *
     * @return array Returns the available hours array.
     *
     * @throws Exception
     */

    protected function consider_multiple_attendants($date, $service, $provider, $exclude_appointment_id = NULL)
    {
        $unavailability_events = $this->CI->appointments_model->get_batch([
            'is_unavailable' => TRUE,
            'DATE(start_datetime)' => $date,
            'id_users_provider' => $provider['id']
        ]);

        $working_plan = json_decode($provider['settings']['working_plan'], TRUE);

        $working_plan_exceptions = json_decode($provider['settings']['working_plan_exceptions'], TRUE);

        $working_day = strtolower(date('l', strtotime($date)));

        $date_working_plan = $working_plan[$working_day] ?? NULL;

        // Search if the $date is an custom availability period added outside the normal working plan.
        if (isset($working_plan_exceptions[$date]))
        {
            $date_working_plan = $working_plan_exceptions[$date];
        }

        if ( ! $date_working_plan)
        {
            return [];
        }

        $periods = [
            [
                'start' => new DateTime($date . ' ' . $date_working_plan['start']),
                'end' => new DateTime($date . ' ' . $date_working_plan['end'])
            ]
        ];

        $periods = $this->remove_breaks($date, $periods, $date_working_plan['breaks']);
        $periods = $this->remove_unavailability_events($periods, $unavailability_events);

        $hours = [];

        $interval_value = $service['availabilities_type'] == AVAILABILITIES_TYPE_FIXED ? $service['duration'] : '15';
        $interval = new DateInterval('PT' . (int)$interval_value . 'M');
        $duration = new DateInterval('PT' . (int)$service['duration'] . 'M');

        foreach ($periods as $period)
        {
            $slot_start = clone $period['start'];
            $slot_end = clone $slot_start;
            $slot_end->add($duration);

            while ($slot_end <= $period['end'])
            {
                // Make sure there is no other service appointment for this time slot.
                $other_service_attendants_number = $this->CI->appointments_model->get_other_service_attendants_number(
                    $slot_start,
                    $slot_end,
                    $service['id'],
                    $provider['id'],
                    $exclude_appointment_id
                );

                if ($other_service_attendants_number > 0)
                {
                    $slot_start->add($interval);
                    $slot_end->add($interval);
                    continue;
                }

                // Check reserved attendants for this time slot and see if current attendants fit.
                $appointment_attendants_number = $this->CI->appointments_model->get_attendants_number_for_period(
                    $slot_start,
                    $slot_end,
                    $service['id'],
                    $provider['id'],
                    $exclude_appointment_id
                );

                if ($appointment_attendants_number < $service['attendants_number'])
                {
                    $hours[] = $slot_start->format('H:i');
                }

                $slot_start->add($interval);
                $slot_end->add($interval);
            }
        }

        return $hours;
    }

    /**
     * Remove breaks from available time periods.
     *
     * @param string $selected_date Selected data (Y-m-d format).
     * @param array $periods Time periods of the current date.
     * @param array $breaks Breaks array for the current date.
     *
     * @return array Returns the available time periods without the breaks.
     * @throws Exception
     */

    public function remove_breaks($selected_date, $periods, $breaks)
    {
        if ( ! $breaks)
        {
            return $periods;
        }

        foreach ($breaks as $break)
        {
            $break_start = new DateTime($selected_date . ' ' . $break['start']);
            $break_end = new DateTime($selected_date . ' ' . $break['end']);

            foreach ($periods as &$period)
            {
                $period_start = $period['start'];
                $period_end = $period['end'];

                if ($break_start <= $period_start && $break_end >= $period_start && $break_end <= $period_end)
                {
                    // left
                    $period['start'] = $break_end;
                    continue;
                }

                if ($break_start >= $period_start && $break_start <= $period_end && $break_end >= $period_start && $break_end <= $period_end)
                {
                    // middle
                    $period['end'] = $break_start;
                    $periods[] = [
                        'start' => $break_end,
                        'end' => $period_end
                    ];
                    continue;
                }

                if ($break_start >= $period_start && $break_start <= $period_end && $break_end >= $period_end)
                {
                    // right
                    $period['end'] = $break_start;
                    continue;
                }

                if ($break_start <= $period_start && $break_end >= $period_end)
                {
                    // break contains period
                    $period['start'] = $break_end;
                    continue;
                }
            }
        }

        return $periods;
    }

    /**
     * Remove the unavailability entries from the available time periods of the selected date.
     *
     * @param array $periods Available time periods.
     * @param array $unavailability_events Unavailability events of the current date.
     *
     * @return array Returns the available time periods without the unavailability events.
     *
     * @throws Exception
     */

    public function remove_unavailability_events($periods, $unavailability_events)
    {
        foreach ($unavailability_events as $unavailability_event)
        {
            $unavailability_start = new DateTime($unavailability_event['start_datetime']);
            $unavailability_end = new DateTime($unavailability_event['end_datetime']);

            foreach ($periods as &$period)
            {
                $period_start = $period['start'];
                $period_end = $period['end'];

                if ($unavailability_start <= $period_start && $unavailability_end >= $period_start && $unavailability_end <= $period_end)
                {
                    // left
                    $period['start'] = $unavailability_end;
                    continue;
                }

                if ($unavailability_start >= $period_start && $unavailability_start <= $period_end && $unavailability_end >= $period_start && $unavailability_end <= $period_end)
                {
                    // middle
                    $period['end'] = $unavailability_start;
                    $periods[] = [
                        'start' => $unavailability_end,
                        'end' => $period_end
                    ];
                    continue;
                }

                if ($unavailability_start >= $period_start && $unavailability_start <= $period_end && $unavailability_end >= $period_end)
                {
                    // right
                    $period['end'] = $unavailability_start;
                    continue;
                }

                if ($unavailability_start <= $period_start && $unavailability_end >= $period_end)
                {
                    // Unavailability contains period
                    $period['start'] = $unavailability_end;
                    continue;
                }
            }
        }

        return $periods;
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
