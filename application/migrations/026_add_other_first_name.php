<?php
defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com> * @copyright   Copyright (c) 2013 - 2020, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3 * @link        http://easyappointments.org * @since       v1.4.0
 * ---------------------------------------------------------------------------- */

/**
 * Class Add_Color_And_Is_Private_Columns_To_Service_Categories
 *
 * @property CI_DB_query_builder $db
 * @property CI_DB_forge $dbforge
 */
class Migration_Add_other_first_name extends CI_Migration
{
    /**
     * Upgrade method.
     */
    public function up()
    {
        $fields = [];

        // Add customers_count column to users table if not exists.
        if (!$this->db->field_exists('other_first_name', 'users'))
            $fields['other_first_name'] = ['type' => 'VARCHAR', 'constraint' => '256', 'null' => TRUE];

        // If some column need to be added.
        if (count($fields)) $this->dbforge->add_column('users', $fields);
    }

    /**
     * Downgrade method.
     */
    public function down()
    {
        $this->dbforge->drop_column('users', 'other_first_name');
    }
}
