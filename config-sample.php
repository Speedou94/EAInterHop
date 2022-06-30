<?php
/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2020, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

/**
 * Easy!Appointments Configuration File
 *
 * Set your installation BASE_URL * without the trailing slash * and the database
 * credentials in order to connect to the database. You can enable the DEBUG_MODE
 * while developing the application.
 *
 * Set the default language by changing the LANGUAGE constant. For a full list of
 * available languages look at the /application/config/config.php file.
 *
 * IMPORTANT:
 * If you are updating from version 1.0 you will have to create a new "config.php"
 * file because the old "configuration.php" is not used anymore.
 */
class Config {

    // ------------------------------------------------------------------------
    // GENERAL SETTINGS
    // ------------------------------------------------------------------------

    const BASE_URL      = 'http://url-to-easyappointments-directory';
    const LANGUAGE      = 'english';
    const DEBUG_MODE    = FALSE;

    // ------------------------------------------------------------------------
    // DATABASE SETTINGS
    // ------------------------------------------------------------------------

    const DB_HOST       = 'localhost';
    const DB_NAME       = 'easyappointments';
    const DB_USERNAME   = 'root';
    const DB_PASSWORD   = 'root';

    // ------------------------------------------------------------------------
    // GOOGLE CALENDAR SYNC
    // ------------------------------------------------------------------------

    const GOOGLE_SYNC_FEATURE   = FALSE; // Enter TRUE or FALSE
    const GOOGLE_PRODUCT_NAME   = '';
    const GOOGLE_CLIENT_ID      = '';
    const GOOGLE_CLIENT_SECRET  = '';
    const GOOGLE_API_KEY        = '';

    // ------------------------------------------------------------------------
    // LOCALE SETTINGS
    // Please note that the defaults values can't be undone after the EA
    // installation. However, set the enabler to false will re enable the
    // corresponding field in the backend pages.
    // ------------------------------------------------------------------------

    const ENABLE_DEFAULT_TIMEZONE       = FALSE; // Enter TRUE or FALSE
    const DEFAULT_TIMEZONE              = 'Europe/Paris'; // Check available timezones in Application/librairies/Timezones.php

    const ENABLE_DEFAULT_TIME_FORMAT    = FALSE; // Enter TRUE or FALSE
    const DEFAULT_TIME_FORMAT           = 'military'; // military => HH:MM, regular => H:MM AM/PM

    const ENABLE_DEFAULT_FIRST_WEEKDAY = FALSE; // Enter TRUE or FALSE
    const DEFAULT_FIRST_WEEKDAY = 'monday'; // Check available weekdays in Application/librairies/Weekdays.php
}

/* End of file config.php */
/* Location: ./config.php */
