<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class used to add debug facilities for developers.
 *
 * @property User_model        $user
 * @property Services_model    $services
 * @property Providers_model   $providers
 * @property Settings_model    $settings
 * @property Secretaries_model $secretaries
 * @property Admins_model      $admins
 */
class Debug extends EA_Controller
{
    const MAX_CATEGORIES = 20;
    const MAX_SERVICES = 20;
    const MAX_PROVIDERS = 20;
    const MAX_SECRETARIES = 20;
    const MAX_ADMINS = 4;
    const MAX_CUSTOMERS = 80;

    public function index()
    {
        $this->load->model('User_model', 'users');
        $this->load->model('Services_model', 'services');
        $this->load->model('Providers_model', 'providers');
        $this->load->model('Settings_model', 'settings');
        $this->load->model('Secretaries_model', 'secretaries');
        $this->load->model('Admins_model', 'admins');

        $faker = Faker\Factory::create('fr_FR');

        $slugs = [DB_SLUG_CUSTOMER, DB_SLUG_PROVIDER, DB_SLUG_ADMIN, DB_SLUG_SECRETARY];

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
            $settings = [
                'username' => $faker->userName(),
                'password' => 'password',
                'notifications' => TRUE,
                'google_sync' => FALSE,
                'sync_past_days' => 30,
                'sync_future_days' => 90,
                'working_plan' => $this->settings->get_setting('company_working_plan'),
                'calendar_view' => CALENDAR_VIEW_DEFAULT];

            $provider = [
                "first_name" => $faker->firstName(),
                "last_name" => $faker->lastName(),
                "email" => $faker->email(),
                "mobile_number" => $faker->phoneNumber(),
                "phone_number" => $faker->phoneNumber(),
                "address" => $faker->address(),
                "city" => $faker->city(),
                "zip_code" => $faker->postcode(),
                "state" => $faker->country(),
                "notes" => $faker->text(),
                "timezone" => $faker->timezone(),
                "language" => $faker->languageCode(),
                "id_roles" => DB_SLUG_PROVIDER,
                "settings" => $settings,
                "services" => [$services[$faker->numberBetween(0, self::MAX_SERVICES - 1)]['id']]];

            // Put the provider in the database.
            $provider["id"] = $this->providers->add($provider);
            // And put it in the array of providers.
            $providers[] = $provider;
        }

        /*********************************/
        /* Create available secretaries. */
        /*********************************/

        for ($i = 0; $i < self::MAX_SECRETARIES; $i++)
        {
            $settings = [
                'username' => $faker->userName(),
                'password' => 'password',
                'notifications' => TRUE,
                'google_sync' => FALSE,
                'sync_past_days' => 30,
                'sync_future_days' => 90,
                'calendar_view' => CALENDAR_VIEW_DEFAULT];

            $secretary = [
                "first_name" => $faker->firstName(),
                "last_name" => $faker->lastName(),
                "email" => $faker->email(),
                "mobile_number" => $faker->phoneNumber(),
                "phone_number" => $faker->phoneNumber(),
                "address" => $faker->address(),
                "city" => $faker->city(),
                "zip_code" => $faker->postcode(),
                "state" => $faker->country(),
                "notes" => $faker->text(),
                "timezone" => $faker->timezone(),
                "language" => $faker->languageCode(),
                "id_roles" => DB_SLUG_PROVIDER,
                "settings" => $settings,
                "providers" => [$providers[$faker->numberBetween(0, self::MAX_PROVIDERS - 1)]['id']]];


            // Put the secretary in the database.
            $secretary["id"] = $this->secretaries->add($secretary);
            // And put it in the array of secretaries.
            $secretaries[] = $secretary;
        }

        /*************************/
        /* Add some admin users. */
        /*************************/

        for ($i = 0; $i < self::MAX_ADMINS; $i++)
        {
            $settings = [
                'username' => $faker->userName(),
                'password' => 'password',
                'notifications' => TRUE,
                'google_sync' => FALSE,
                'sync_past_days' => 30,
                'sync_future_days' => 90,
                'calendar_view' => CALENDAR_VIEW_DEFAULT];

            $admin = [
                "first_name" => $faker->firstName(),
                "last_name" => $faker->lastName(),
                "email" => $faker->email(),
                "mobile_number" => $faker->phoneNumber(),
                "phone_number" => $faker->phoneNumber(),
                "address" => $faker->address(),
                "city" => $faker->city(),
                "zip_code" => $faker->postcode(),
                "state" => $faker->country(),
                "notes" => $faker->text(),
                "timezone" => $faker->timezone(),
                "language" => $faker->languageCode(),
                "id_roles" => DB_SLUG_PROVIDER,
                "settings" => $settings];

            // Put the admin in the database.
            $admin["id"] = $this->admins->add($admin);
            // And put it in the array of providers.
            $admins[] = $admin;
        }


    }
}
