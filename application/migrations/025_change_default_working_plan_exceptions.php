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
 * Class Change_Default_Working_Plan
 *
 * @property CI_DB_query_builder $db
 * @property CI_DB_forge         $dbforge
 */
class Migration_Change_Default_Working_Plan_Exceptions extends CI_Migration
{
    /**
     * Upgrade method.
     */
    public function up()
    {
        $row  = $this->db->get_where('user_settings', ['name' => 'working_plan_exceptions'])->row_array();

        $wp = json_decode($row['value'], true);

        foreach ($wp as $key => &$value) $value['
        
        specializeds'] = [];

        $row['value'] = json_encode($wp);

        $this->db->update('user_settings', ['name' => 'working_plan_exceptions']);
    }

    /**
     * Downgrade method.
     */
    public function down()
    {
        $row  = $this->db->get_where('user_settings', ['name' => 'working_plan_exceptions'])->row_array();

        $wp = json_decode($row['value'], true);

        foreach ($wp as $key => &$value) unset($value['specializeds']);

        $row['value'] = json_encode($wp);

        $this->db->update('user_settings', ['name' => 'working_plan_exceptions']);
    }
}
