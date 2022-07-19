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
class Migration_Add_Color_And_Is_Private_Columns extends CI_Migration
{
    /**
     * Upgrade method.
     */
    public function up()
    {
        $fields = [];

        // Add color column to categories table if not exists.
        if (!$this->db->field_exists('color', 'service_categories'))
            $fields['color'] = ['type' => 'VARCHAR', 'constraint' => '7', 'null' => TRUE];

        // Add is_private column to categories table if not exists.
        if (!$this->db->field_exists('is_private', 'service_categories'))
            $fields['is_private'] = ['type' => 'TINYINT', 'constraint' => '1', 'null' => TRUE];

        // If some column need to be added.
        if (count($fields)) $this->dbforge->add_column('service_categories', $fields);
    }

    /**
     * Downgrade method.
     */
    public function down()
    {
        $this->dbforge->drop_column('service_categories', 'color');
        $this->dbforge->drop_column('service_categories', 'is_private');
    }
}
