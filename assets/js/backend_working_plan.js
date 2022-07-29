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

window.BackendWorkingPlan = window.BackendWorkingPlan || {};

/**
 * Backend working plan
 *
 * Backend Customers javascript namespace. Contains the main functionality of the backend customers
 * page. If you need to use this namespace in a different page, do not bind the default event handlers
 * during initialization.
 *
 * @module BackendCustomers
 */
(function (exports)
{
    'use strict';

    /**
     * The page helper contains methods that implement each record type functionality
     * (for now there is only the CustomersHelper).
     *
     * @type {Object}
     */
    var helper = {};

    /**
     * Use this class instance for performing actions on the working plan.
     *
     * @type {WorkingPlan}
     */
    exports.wp = {};

    /**
     * This method initializes the backend working plan page. If you use this namespace
     * in a different page do not use this method.
     *
     * @param {Boolean} defaultEventHandlers Optional (false), whether to bind the default
     * event handlers or not.
     */

    exports.initialize = function ()
    {
        exports.wp = new WorkingPlan();
        exports.wp.bindEventHandlers();

        // Add the available languages to the language dropdown.
        availableLanguages.forEach(function (language)
        {
            $('#language').append(new Option(language, language));
        });

        helper = new WorkingPlanHelper();
        helper.resetForm();

        if (GlobalVariables.user.role_slug === Backend.DB_SLUG_PROVIDER) helper.filter('', $('#provider-id').val(), true);
        else helper.filter('');

        helper.bindEventHandlers();
    };
})(window.BackendWorkingPlan);
