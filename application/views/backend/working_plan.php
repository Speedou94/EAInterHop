<script src="<?= asset_url('assets/js/backend_working_plan_helper.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_working_plan.js') ?>"></script>
<script src="<?= asset_url('assets/js/working_plan.js') ?>"></script>
<script src="<?= asset_url('assets/js/working_plan_exceptions_modal.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-ui/jquery-ui-timepicker-addon.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-jeditable/jquery.jeditable.min.js') ?>"></script>

<script>
    var GlobalVariables = {
        csrfToken: <?= json_encode($this->security->get_csrf_hash()) ?>,
        baseUrl: <?= json_encode($base_url) ?>,
        providers: <?= json_encode($providers) ?>,
        dateFormat: <?= json_encode($date_format) ?>,
        timeFormat: <?= json_encode($time_format) ?>,
        firstWeekday: <?= json_encode($first_weekday) ?>,
        workingPlan: <?= json_encode($working_plan) ?>,
        workingPlanExceptions: <?= json_encode($working_plan_exceptions) ?>,

        user: {
            id: <?= $user_id ?>,
            email: <?= json_encode($user_email) ?>,
            timezone: <?= json_encode($timezone) ?>,
            role_slug: <?= json_encode($role_slug) ?>,
            privileges: <?= json_encode($privileges) ?>
        }
    };

    $(function () { BackendWorkingPlan.initialize(true); })
</script>

<div class="container-fluid backend-page" id="workingplan-page">
    <div class="tab-pane active" id="workingplan">
        <div class="row">
            <!-- search filters -->
            <div id="filter-providers" class="filter-records column col-12 col-md-5">
                <!-- Search input -->
                <form class="mb-4">
                    <div class="input-group">
                        <input type="text" class="key form-control">

                        <div class="input-group-addon">
                            <div>
                                <button class="filter btn btn-outline-secondary" type="submit" data-tippy-content="<?= lang('filter') ?>">
                                    <i class="fas fa-search"></i>
                                </button>
                                <button class="clear btn btn-outline-secondary" type="button" data-tippy-content="<?= lang('clear') ?>">
                                    <i class="fas fa-redo-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <h3><?= lang('providers') ?></h3>

                <!-- Providers list -->
                <div class="results"></div>
            </div>

            <div class="record-details column col-12 col-md-7">

                <div class="float-md-left mb-4 mr-4">
                    <div class="add-edit-delete-group btn-group">
                        <button id="edit-provider" class="btn btn-outline-secondary" disabled="disabled">
                            <i class="fas fa-edit mr-2"></i>
                            <?= lang('edit') ?>
                        </button>
                    </div>

                    <div class="save-cancel-group btn-group" style="display:none;">
                        <button id="save-provider" class="btn btn-primary">
                            <i class="fas fa-check-square mr-2"></i>
                            <?= lang('save') ?>
                        </button>
                        <button id="cancel-provider" class="btn btn-outline-secondary">
                            <i class="fas fa-ban mr-2"></i>
                            <?= lang('cancel') ?>
                        </button>
                    </div>
                </div>

                <?php
                // This form message is outside the details view, so that it can be
                // visible when the user has working plan view active.
                ?>

                <div class="form-message alert" style="display:none;"></div>

                <!-- Provider's working plan -->
                <div class="tab-content">
                    <div class="working-plan-view tab-pane fade show active clearfix" id="working-plan">
                        <h3><?= lang('working_plan') ?></h3>
                        <button id="reset-working-plan" class="btn btn-primary" data-tippy-content="<?= lang('reset_working_plan') ?>">
                            <i class="fas fa-redo-alt mr-2"></i>
                            <?= lang('reset_plan') ?>
                        </button>
                        <table class="working-plan table table-striped mt-2">
                            <thead>
                            <tr>
                                <th><?= lang('day') ?></th>
                                <th><?= lang('start') ?></th>
                                <th><?= lang('end') ?></th>
                            </tr>
                            </thead>
                            <tbody><!-- Dynamic Content --></tbody>
                        </table>

                        <br>

                        <h3><?= lang('breaks') ?></h3>

                        <p>
                            <?= lang('add_breaks_during_each_day') ?>
                        </p>

                        <div>
                            <button type="button" class="add-break btn btn-primary">
                                <i class="fas fa-plus-square mr-2"></i>
                                <?= lang('add_break') ?>
                            </button>
                        </div>

                        <br>

                        <table class="breaks table table-striped">
                            <thead>
                            <tr>
                                <th><?= lang('day') ?></th>
                                <th><?= lang('start') ?></th>
                                <th><?= lang('end') ?></th>
                                <th><?= lang('actions') ?></th>
                            </tr>
                            </thead>
                            <tbody><!-- Dynamic Content --></tbody>
                        </table>

                        <br>

                        <h3><?= lang('working_plan_exceptions') ?></h3>

                        <p>
                            <?= lang('add_working_plan_exceptions_during_each_day') ?>
                        </p>

                        <div>
                            <button type="button" class="add-working-plan-exception btn btn-primary mr-2">
                                <i class="fas fa-plus-square"></i>
                                <?= lang('add_working_plan_exception') ?>
                            </button>
                        </div>

                        <br>

                        <table class="working-plan-exceptions table table-striped">
                            <thead>
                            <tr>
                                <th><?= lang('day') ?></th>
                                <th><?= lang('start') ?></th>
                                <th><?= lang('end') ?></th>
                                <th><?= lang('actions') ?></th>
                            </tr>
                            </thead>
                            <tbody><!-- Dynamic Content --></tbody>
                        </table>

                        <?php require __DIR__ . '/working_plan_exceptions_modal.php' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
