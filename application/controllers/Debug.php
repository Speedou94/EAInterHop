<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class used to add debug facilities for developers.
 *
 * @property User_model         $user
 * @property Services_model     $services
 * @property Providers_model    $providers
 * @property Settings_model     $settings
 * @property Secretaries_model  $secretaries
 * @property Admins_model       $admins
 * @property Customers_model    $customers
 * @property Appointments_model $appointments
 */
class Debug extends EA_Controller
{
    const MAX_CATEGORIES = 20;
    const MAX_SERVICES = 20;
    const MAX_PROVIDERS = 20;
    const MAX_SECRETARIES = 20;
    const MAX_ADMINS = 4;
    const MAX_CUSTOMERS = 80;

    /**
     * Populate the database with random data. Simply go to the BASE_URL/debug/populate to execute this task.
     *
     * @return void
     * @throws Exception
     */

    public function populate()
    {
        $this->load->model('User_model', 'users');
        $this->load->model('Services_model', 'services');
        $this->load->model('Providers_model', 'providers');
        $this->load->model('Settings_model', 'settings');
        $this->load->model('Secretaries_model', 'secretaries');
        $this->load->model('Admins_model', 'admins');
        $this->load->model('Customers_model', 'customers');
        $this->load->model('Appointments_model', 'appointments');
        $this->load->config('config');

        $this->load->library('timezones');

        $this->load->helper('debug_helper');

        if ($this->config->item('enable_default_timezone'))
        {
            $key = preg_split('/\//', $this->config->item('default_timezone'))[0];
            $timezones = [$key => [$this->config->item('default_timezone') => $this->timezones->to_grouped_array()[$key][$this->config->item('default_timezone')]]];
        }
        else
        {
            $timezones = $this->timezones->to_grouped_array();
        }

        $faker = Faker\Factory::create('fr_FR');

        /********************************/
        /* Create available categories. */
        /********************************/

        for ($i = 0; $i < self::MAX_CATEGORIES; $i++)
        {
            $category = [
                'name' => $faker->unique()->word,
                'description' => $faker->text(100),
            ];

            // Put the category in the database.
            $category['id'] = $this->services->add_category($category);

            // And add it to the list of available categories.
            $categories[] = $category;
        }

        /******************************/
        /* Create available services. */
        /******************************/

        for ($i = 0; $i < self::MAX_SERVICES; $i++)
        {
            $service = [
                'name' => $faker->jobTitle(),
                'duration' => $faker->numberBetween(1, 8) * 15,
                'price' => $faker->randomFloat(2, 0, 100),
                'currency' => $faker->currencyCode(),
                'availabilities_type' => 'flexible',
                'attendants_number' => $faker->numberBetween(1, 5),
                'id_service_categories' => $categories[$faker->numberBetween(0, self::MAX_CATEGORIES - 1)]['id']
            ];

            // Put new service into the DB and retrieve its ID.
            $service['id'] = $this->services->add($service);
            // And put it in the array.
            $services[] = $service;
        }

        /*******************************/
        /* Create available providers. */
        /*******************************/

        for ($i = 0; $i < self::MAX_PROVIDERS; $i++)
        {
            // Create a provider user.
            $provider = createUnroledUser($faker, $timezones, $this->config->item('available_languages'));
            $provider['settings']['working_plan'] = $this->settings->get_setting('company_working_plan');
            $provider['services'] = [$services[$faker->numberBetween(0, self::MAX_SERVICES - 1)]['id']];

            // Put the provider in the database.
            $provider['id'] = $this->providers->add($provider);
            // And put it in the array of providers.
            $providers[] = $provider;
        }

        /*********************************/
        /* Create available secretaries. */
        /*********************************/

        for ($i = 0; $i < self::MAX_SECRETARIES; $i++)
        {
            // Create a secretary user.
            $secretary = createUnroledUser($faker, $timezones, $this->config->item('available_languages'));
            $secretary['providers'] = [$providers[$faker->numberBetween(0, self::MAX_PROVIDERS - 1)]['id']];

            // Put the secretary in the database.
            $secretary['id'] = $this->secretaries->add($secretary);
            // And put it in the array of secretaries.
            $secretaries[] = $secretary;
        }

        /*************************/
        /* Add some admin users. */
        /*************************/

        for ($i = 0; $i < self::MAX_ADMINS; $i++)
        {
            // Create an admin user.
            $admin = createUnroledUser($faker, $timezones, $this->config->item('available_languages'));

            // Put the admin in the database.
            $admin["id"] = $this->admins->add($admin);
            // And put it in the array of providers.
            $admins[] = $admin;
        }

        /**********************************/
        /* Add customers and appointments */
        /**********************************/

        for ($i = 0; $i < self::MAX_CUSTOMERS; $i++)
        {
            // Create a customer user.
            $customer = createUnroledUser($faker, $timezones, $this->config->item('available_languages'));
            unset($customer['settings']);

            // Put the customer in the database.
            $customer["id"] = $this->customers->add($customer);
            // And put it in the array of customers.
            $customers[] = $customer;

            //  Find a provider to catch services.
            $provider = $providers[rand(0, count($providers) - 1)];

            // Retrieve the provider's working plan.
            $workPlane = json_decode($provider['settings']['working_plan'], true);

            // Compute all the possible appointment dates.
            foreach ($workPlane as $day => $list)
            {
                $work[$day] = [['start' => $list['start'], 'end' => $list['end']]];

                foreach ($list['breaks'] as $break) $work[$day] = rangeSplit($work[$day], $break);
            }

            foreach ($work as $day => $list)
            {
                // Find a random service supplied by the selected provider.
                $service = $this->services->get_batch(['id' => $provider['services'][rand(0, count($provider['services']) - 1)]]);

                // Determine the time slot for the appointment.
                $serviceDuration = $service[0]['duration'] * 60;
                $startTime = preg_split('/:/', $list[0]['start']);
                $startTime = ($startTime[0] * 60 + $startTime[1] * 1) * 60;
                $endTime = $startTime + $serviceDuration;

                // Choose an hour randomly in the time slot.
                $randomHour = rand($startTime, $endTime);

                // Determine the appointment start and end date.
                $startDate = $faker->dateTimeBetween('now', '+1 month');
                $startDate->setTimestamp($startDate->getTimestamp() - ($startDate->getTimestamp() % (24 * 60 * 60)) + $randomHour);
                $endDate = clone $startDate;
                $endDate->setTimestamp($endDate->getTimestamp() + $serviceDuration);

                $appointment = [
                    'id_users_provider' => $provider['id'],
                    'id_users_customer' => $customer['id'],
                    'id_services' => $service[0]['id'],
                    'start_datetime' => $startDate->format('Y-m-d H:i:s'),
                    'end_datetime' => $endDate->format('Y-m-d H:i:s'),
                    'notes' => $faker->text(100),
                    'location' => $service[0]['location'],
                    'is_unavailable' => FALSE,
                ];

                $this->appointments->add($appointment);
            }
        }

        echo 'done.';
    }

    /**
     * Empty the entire database. Simply go to the BASE_URL/debug/empty to execute this task.
     *
     * @return void
     * @throws Exception
     */

    public function empty()
    {
        $this->load->model('appointments_model', 'appointments');
        $this->load->model('customers_model', 'customers');
        $this->load->model('providers_model', 'providers');
        $this->load->model('secretaries_model', 'secretaries');
        $this->load->model('services_model', 'services');
        $this->load->model('admins_model', 'admins');

        // Retrieve all the appointments.
        $appointments = $this->appointments->get_batch();
        // And delete them.
        foreach ($appointments as $appointment) $this->appointments->delete($appointment['id']);

        // Retrieve all the admins.
        $admins = $this->admins->get_batch();
        // And delete them, but the first.
        foreach ($admins as $admin) if ($admin['id'] != 1) $this->admins->delete($admin['id']);

        // Retrieve all the customers.
        $customers = $this->customers->get_batch();
        // And delete them.
        foreach ($customers as $customer) $this->customers->delete($customer['id']);

        // Retrieve all the providers.
        $providers = $this->providers->get_batch();
        // And delete them.
        foreach ($providers as $provider) $this->providers->delete($provider['id']);

        // Retrieve all the secretaries.
        $secretaries = $this->secretaries->get_batch();
        // And delete them.
        foreach ($secretaries as $secretary) $this->secretaries->delete($secretary['id']);

        // Retrieve all the services.
        $services = $this->services->get_batch();
        // And delete them.
        foreach ($services as $service) $this->services->delete($service['id']);

        echo 'done.';
    }
}
