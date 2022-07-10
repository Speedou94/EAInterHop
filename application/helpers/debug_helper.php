<?php

/**
 * Create a specialised user with predefined settings.
 *
 * @param $faker     Faker used to retrieve random data.
 * @param $timezones Array of timezones.
 * @param $languages Array of languages.
 *
 * @return Array of user properties.
 */

function createUnroledUser($faker, $timezones, $languages)
{
    return [
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
        "timezone" => array_rand($timezones[array_rand($timezones)]),
        "language" => $languages[array_rand($languages)],
        "settings" => [
            'username' => $faker->userName(),
            'password' => 'password',
            'notifications' => TRUE,
            'google_sync' => FALSE,
            'sync_past_days' => 30,
            'sync_future_days' => 90,
            'calendar_view' => CALENDAR_VIEW_DEFAULT]];
}

/**
 * @param $list  List of time slots.
 * @param $range Break slot to insert.
 *
 * @return array
 */

function rangeSplit($list, $range)
{
    for ($i = 0; $i < count($list); $i++)
    {
        $current = $list[$i];

        if ((strcmp($range['start'], $current['start']) > 0) && (strcmp($range['end'], $current['end']) < 0))
        {
            $leftPart = array_slice($list, 0, $i + 1);
            $endPart = array_slice($list, $i + 1);
            $list = array_merge($leftPart, [['start' => $range['end'], 'end' => $current['end']]], $endPart);
            $list[$i]['end'] = $range['start'];
        }
        else if ((strcmp($range['start'], $current['start']) <= 0) && (strcmp($range['end'], $current['start']) > 0) && (strcmp($range['end'], $current['end']) < 0))
        {
            $list[$i]['start'] = $range['end'];
        }
        else if ((strcmp($range['start'], $current['start']) > 0) && (strcmp($range['start'], $current['end']) < 0) && (strcmp($range['end'], $current['end']) > 0))
        {
            $list[$i]['end'] = $range['start'];
        }
        else if ((strcmp($range['start'], $current['start']) <= 0) && (strcmp($range['end'], $current['end']) >= 0))
        {
            unset($list[$i]);
        }
    }

    return $list;
}
