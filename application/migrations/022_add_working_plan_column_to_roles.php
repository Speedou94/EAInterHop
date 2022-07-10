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
 * Class Add_Working_Plan_Column_To_Roles
 *
 * @property CI_DB_query_builder $db
 * @property CI_DB_forge         $dbforge
 */
class Migration_Add_Working_Plan_Column_To_Roles extends CI_Migration
{
    /**
     * Upgrade method.
     */
    public function up()
    {
        // If column already exist, do nothing.
        if ($this->db->field_exists('working_plan', 'roles')) return;

        // Column to be added.
        $fields = ['working_plan' => ['type' => 'INT', 'constraint' => '4', 'null' => TRUE]];

        // Create it in the database.
        $this->dbforge->add_column('roles', $fields);

        // Retrive all roles to update the working plan privileges.
        $roles = $this->db->get('roles')->result_array();

        foreach ($roles as $role => $privileges)
        {
            // Set the appropriate working plan privilege.
            $privileges['working_plan'] = (($privileges['slug'] === DB_SLUG_PROVIDER) || ($privileges['slug'] === DB_SLUG_SECRETARY)) ? 15 : 0;

            // And update the database.
            $this->db->update('roles', $privileges, ['id' => $privileges['id']]);
        }
    }

    /**
     * Downgrade method.
     */
    public function down()
    {
        $this->dbforge->drop_column('roles', 'working_plan');
    }
}
