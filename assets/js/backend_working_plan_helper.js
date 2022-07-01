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

(function ()
{
    'use strict';

    /**
     * WorkingPlanHelper Class
     *
     * This class contains the methods that are used in the backend working plan page.
     *
     * @class WorkingPlanHelper
     */
    function WorkingPlanHelper()
    {
        this.filterResults = {};
        this.filterLimit = 20;
    }

    /**
     * Binds the default event handlers of the backend working plan page.
     */
    WorkingPlanHelper.prototype.bindEventHandlers = function ()
    {
        /**
         * Event: Filter Providers Form "Submit"
         *
         * Filter the provider records with the given key string.
         *
         * @param {jQuery.Event} event
         */
        $('#filter-providers').on('submit', 'form', function (event)
        {
            event.preventDefault();
            var key = $('#filter-providers .key').val();
            $('.selected').removeClass('selected');
            this.resetForm();
            this.filter(key);
        }.bind(this));

        /**
         * Event: Clear Filter Button "Click"
         */
        $('#filter-providers').on('click', '.clear', function ()
        {
            this.filter('');
            $('#filter-providers .key').val('');
            this.resetForm();
        }.bind(this));

        /**
         * Event: Filter Provider Row "Click"
         *
         * Display the selected provider data to the user.
         */
        $('#filter-providers').on('click', '.provider-row', function (event)
        {
            if ($('#filter-providers .filter').prop('disabled'))
            {
                $('#filter-providers .results').css('color', '#AAA');
                return;
            }

            var providerId = $(event.currentTarget).attr('data-id');
            var provider = this.filterResults.find(function (filterResult)
            {
                return Number(filterResult.id) === Number(providerId);
            });

            this.display(provider);
            $('#filter-providers .selected').removeClass('selected');
            $(event.currentTarget).addClass('selected');
            $('#edit-provider, #delete-provider').prop('disabled', false);
        }.bind(this));

        /**
         * Event: Edit Provider Button "Click"
         */
        $('#workingplan').on('click', '#edit-provider', function () {
            $('.add-edit-delete-group').hide();
            $('.save-cancel-group').show();

            $('#filter-providers button').prop('disabled', true);
            $('#filter-providers .results').css('color', '#AAA');

            $('#working-plan').find('input, select, textarea').prop('disabled', false);

            $('#working-plan').find('.add-break, .edit-break, .delete-break, .add-working-plan-exception, .edit-working-plan-exception, .delete-working-plan-exception, #reset-working-plan').prop('disabled', false);
            $('#working-plan input:checkbox').prop('disabled', false);
            BackendWorkingPlan.wp.timepickers(false);
        });

        /**
         * Event: Cancel Provider Button "Click"
         *
         * Cancel add or edit of an provider record.
         */
        $('#workingplan').on('click', '#cancel-provider', function () {
            var id = $('#filter-providers .selected').attr('data-id');
            this.resetForm();
            if (id) this.select(id, true);
        }.bind(this));

        /**
         * Event: Save Provider Button "Click"
         */
        $('#workingplan').on('click', '#save-provider', function () {
            var provider = {
                settings: {
                    working_plan: JSON.stringify(BackendWorkingPlan.wp.get()),
                    working_plan_exceptions: JSON.stringify(BackendWorkingPlan.wp.getWorkingPlanExceptions()),
                }
            };

            // Include identifier.
            if ($('#provider-id').val() !== '') provider.id = $('#provider-id').val();

            if (!this.validate()) return;

            this.save(provider);
        }.bind(this));
    };

    /**
     * Save a provider working plan record to the database (via ajax post).
     *
     * @param {Object} provider Contains the customer data.
     */
    WorkingPlanHelper.prototype.save = function (provider)
    {
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_save_working_plan';

        var data = {csrfToken: GlobalVariables.csrfToken, provider: JSON.stringify(provider)};

        $.post(url, data)
            .done(function (response)
            {
                Backend.displayNotification(EALang.provider_saved);
                this.resetForm();
                $('#filter-providers .key').val('');
                this.filter('', response.id, true);
            }.bind(this));
    };

    /**
     * Validate customer data before save (insert or update).
     */
    WorkingPlanHelper.prototype.validate = function ()
    {
        $('#form-message')
            .removeClass('alert-danger')
            .hide();
        $('.has-error').removeClass('has-error');

        return true;
    };

    /**
     * Bring the working plan form back to its initial state.
     */
    WorkingPlanHelper.prototype.resetForm = function ()
    {
        // Reset the form filter and providers list.
        $('#filter-providers .selected').removeClass('selected');
        $('#filter-providers button').prop('disabled', false);
        $('#filter-providers .results').css('color', '');

        // Default buttons
        $('.add-edit-delete-group').show();
        $('.save-cancel-group').hide();
        $('#edit-provider').prop('disabled', true);

        BackendWorkingPlan.wp.timepickers(true);

        $('.working-plan input:text').timepicker('destroy');

        $('.working-plan input:checkbox').prop('disabled', true);
        $('.working-plan-exceptions').find('.edit-working-plan-exception, .delete-working-plan-exception').prop('disabled', true);

        $('.record-details .has-error').removeClass('has-error');
        $('.record-details .form-message').hide();

        // Disable add slot in working plan.
        $('.add-break, .add-working-plan-exception, #reset-working-plan').prop('disabled', true);
        // Empty the working plan.
        $('.working-plan tbody').empty();
        $('.breaks tbody').empty();
        $('.working-plan-exceptions tbody').empty();
    };

    /**
     * Display a provider record into the form.
     *
     * @param {Object} provider Contains the provider record data.
     */
    WorkingPlanHelper.prototype.display = function (provider)
    {
        // Save the provider id for further updates.
        $('#provider-id').val(provider.id);

        // Display working plan
        var workingPlan = $.parseJSON(provider.settings.working_plan);
        BackendWorkingPlan.wp.setup(workingPlan);
        $('.working-plan').find('input').prop('disabled', true);
        $('.breaks').find('.edit-break, .delete-break').prop('disabled', true);
        $('.working-plan-exceptions tbody').empty();
        var workingPlanExceptions = $.parseJSON(provider.settings.working_plan_exceptions);
        BackendWorkingPlan.wp.setupWorkingPlanExceptions(workingPlanExceptions);
        $('.working-plan-exceptions').find('.edit-working-plan-exception, .delete-working-plan-exception').prop('disabled', true);
        $('.working-plan input:checkbox').prop('disabled', true);
        Backend.placeFooterToBottom();
    };

    /**
     * Filter provider records.
     *
     * @param {String} key This key string is used to filter the provider records.
     * @param {Number} selectId Optional, if set then after the filter operation the record with the given
     * ID will be selected (but not displayed).
     * @param {Boolean} display Optional (false), if true then the selected record will be displayed on the form.
     */
    WorkingPlanHelper.prototype.filter = function (key, selectId, display)
    {
        display = display || false;

        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_filter_providers';

        var data = {csrfToken: GlobalVariables.csrfToken, key: key, limit: this.filterLimit};

        $.post(url, data)
            .done(function (response)
            {
                this.filterResults = response;

                $('#filter-providers .results').empty();

                response.forEach(function (provider)
                {
                    $('#filter-providers .results').append(this.getFilterHtml(provider)).append($('<hr/>'));
                }.bind(this));

                if (!response.length)
                {
                    $('#filter-providers .results').append($('<em/>', {'text': EALang.no_records_found}));
                }
                else if (response.length === this.filterLimit)
                {
                    $('<button/>', {
                            'type': 'button',
                            'class': 'btn btn-block btn-outline-secondary load-more text-center',
                            'text': EALang.load_more,
                            'click': function ()
                            {
                                this.filterLimit += 20;
                                this.filter(key, selectId, display);
                            }.bind(this)
                        }
                    ).appendTo('#filter-providers .results');
                }

                if (selectId) this.select(selectId, display);

            }.bind(this));
    };

    /**
     * Get the filter results row HTML code.
     *
     * @param {Object} provider Contains the provider data.
     *
     * @return {String} Returns the record HTML code.
     */
    WorkingPlanHelper.prototype.getFilterHtml = function (provider)
    {
        var name = provider.first_name + ' ' + provider.last_name;

        var info = provider.email;

        info = provider.phone_number ? info + ', ' + provider.phone_number : info;

        return $('<div/>', {
            'class': 'provider-row entry',
            'data-id': provider.id,
            'html': [
                $('<strong/>', {
                    'text': name
                }),
                $('<br/>'),
                $('<span/>', {
                    'text': info
                }),
                $('<br/>'),
            ]
        });
    };

    /**
     * Select a specific record from the current filter results.
     *
     * If the provider id does not exist in the list then no record will be selected.
     *
     * @param {Number} id The record id to be selected from the filter results.
     * @param {Boolean} display Optional (false), if true then the method will display the record
     * on the form.
     */
    WorkingPlanHelper.prototype.select = function (id)
    {
        // Unselect the current selected record.
        $('#filter-providers .selected').removeClass('selected');

        // Select the new record.
        $('#filter-filter-providers .entry[data-id="' + id + '"]').addClass('selected');

        // Search the provider in the filter results that is selected.
        var provider = this.filterResults.find(function (filterResult)
        {
            return Number(filterResult.id) === Number(id);
        });

        // And display it in the form.
        this.display(provider);

        // As a provider is selected, the edit button can be used.
        $('#edit-provider').prop(' disabled ', false);
    };

    window.WorkingPlanHelper = WorkingPlanHelper;
})();
