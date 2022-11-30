<?php defined('BASEPATH') or exit('No direct script access allowed');

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
 * Customers Model
 *
 * @package Models
 */
class Customers_model extends EA_Model {
    /**
     * Customers_Model constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('data_validation');
    }

    /**
     * Add a customer record to the database.
     *
     * This method adds a customer to the database. If the customer doesn't exists it is going to be inserted, otherwise
     * the record is going to be updated.
     *
     * @param array $customer Associative array with the customer's data. Each key has the same name with the database
     * fields.
     *
     * @return int Returns the customer id.
     * @throws Exception
     */
    public function add($customer)
    {
        // Validate the customer data before doing anything.
        $this->validate($customer);

        // Check if a customer already exists (by phone number).
        if ($this->exists($customer) && ! isset($customer['id']))
        {
            // Find the customer id from the database.
            $customer['id'] = $this->find_record_id($customer);
        }

        // Insert or update the customer record.
        if ( ! isset($customer['id']))
        {
            $customer['id'] = $this->insert($customer);
        }
        else
        {
            $this->update($customer);
        }

        return $customer['id'];
    }

    /**
     * * Method to limit the addition of customer by provider
     * @return bool
     * @throws Exception
     *
     */
    public function check_count_customer_by_provider()
    {

        //get session id

        $provider = $this->session->user_id;
        //count of customer
        $sql_count_customer = $this->db
            ->select('id_users_customer')
            ->from('users')
            ->join('appointments','users.id = appointments.id_users_provider','inner')
            ->where('users.id',$provider)
            ->group_by('id_users_customer')
            ->count_all_results();

        //limit of provider (customer_count in the database)
            $sql_column_count_customer = $this->db
            ->select('customers_count')
            ->from('users')
            ->where('id',$provider)
            ->get()->row();

         if($sql_count_customer > (int)$sql_column_count_customer->customers_count)
        {
            throw new Exception('You can no longer add patients.');
        }

        return TRUE;

    }

    /**
     *  Method to limit the addition of customer by provider by his secretary
     * @return bool
     * @throws Exception
     *
     */
    public function check_count_customer_by_secretary()
    {
        //get session id
        $secretary = $this->session->user_id;

        //select id_users_customer from `ea_appointments` a join `ea_secretaries_providers` sp on a.id_users_provider = a.id_users_provider where sp.id_users_secretary;
        //count of customer
        $sql_count_customer = $this->db
            ->select('id_users_customer')
            ->from('appointments')
            ->join('secretaries_providers','appointments.id_users_provider = secretaries_providers.id_users_provider','inner')
            ->where('id_users_secretary',$secretary)
            ->group_by('id_users_customer')
            ->count_all_results();

       // SELECT customers_count FROM `ea_users` u join `ea_secretaries_providers` sp on u.id = sp.id_users_provider where sp.id_users_secretary;
        //limit of provider (customer_count in the database)
        $sql_column_count_customer = $this->db
            ->select('customers_count')
            ->from('users')
            ->join('secretaries_providers','users.id = secretaries_providers.id_users_provider')
            ->where('id_users_secretary',$secretary)
            ->get()->row();

        if($sql_count_customer > (int)$sql_column_count_customer->customers_count)
        {
            throw new Exception('You can no longer add patients.');
        }

        return TRUE;

    }

    /**
     * @return mixed
     */
   /* public function display_customers_by_provider($user_id = 0)
    {


     if($user_id > 0)
     {


            $key = $this->db->escape_str($this->input->post("key"));
            $key = strtoupper($key);

            $order_by = 'first_name ASC, last_name ASC';

            $limit = $this->input->post('limit');
            $provider = $this->session->user_id;
            if ($limit === NULL) {
                $limit = 1000;
            }

           // $sql = 'SELECT DISTINCT u.* FROM `ea_users` u join `ea_appointments` a on u.id = a.id_users_customer and a.id_users_provider';

           /* $where =
                 '(
                 u.first_name LIKE upper("%' . $key . '%") OR ' .
                 'u.last_name  LIKE upper("%' . $key . '%") OR ' .
                 'u.email LIKE upper("%' . $key . '%") OR ' .
                 'u.phone_number LIKE upper("%' . $key . '%") OR ' .
                 'u.address LIKE upper("%' . $key . '%") OR ' .
                 'u.city LIKE upper("%' . $key . '%") OR ' .
                 'u.zip_code LIKE upper("%' . $key . '%") OR ' .
                 'u.notes LIKE upper("%' . $key . '%"))' ;
             $sql.= ' where '.$where;
             $sql.= ' order by '.$order_by;
             $sql.= ' limit '.$limit;

             $customers = $this->db->query($sql)->result_array();

            //SELECT u.* FROM `ea_users` u join `ea_appointments` a on u.id = a.id_users_customer
            // WHERE a.id_users_provider=4
            // and u.last_name LIKE ('t%')
            // OR u.first_name LIKE ('%a')
            // GROUP BY a.id_users_customer
            // ORDER BY ('u.first_name,u.last_name')
            // LIMIT 5;*/



            /* $customer = $this->db
                 ->select('*')
                 ->from('users')
                 ->join('appointments', 'users.id = appointments.id_users_customer', 'inner')
                 ->where('appointments.id_users_provider', $user_id)
                 ->where('users.first_name LIKE upper("%' . $key . '%")',$where)
                 ->or_where('users.email  LIKE upper("%' . $key . '%")',$where)
                 ->or_where('users.phone_number LIKE upper("%' . $key . '%")',$where)
                 ->or_where('users.address LIKE upper("%' . $key . '%")',$where)
                 ->or_where('users.city LIKE upper("%' . $key . '%")',$where)
                 ->or_where('users.zip_code LIKE upper("%' . $key . '%")',$where)
                 ->group_by('appointments.id_users_customer')
                 ->order_by($order_by)
                 ->limit($limit)
                 ->getResultArray();

         ob_start();
         var_dump($customer);
         $mydebug = ob_get_clean();
         error_log($mydebug);
        }
        else
        {
           $customers = $this->customers_model->get_batch($where, $limit, NULL, $order_by);

        }

        return $customers;
    }*/

    /**
     * Validate customer data before the insert or update operation is executed.
     *
     * @param array $customer Contains the customer data.
     *
     * @return bool Returns the validation result.
     *
     * @throws Exception If customer validation fails.
     */
    public function validate($customer)
    {
        // If a customer id is provided, check whether the record exist in the database.
        if (isset($customer['id']))
        {
            $num_rows = $this->db->get_where('users', ['id' => $customer['id']])->num_rows();

            if ($num_rows === 0)
            {
                throw new Exception('Provided customer id does not '
                    . 'exist in the database.');
            }
        }

        $phone_number_required = $this->db->get_where('settings', ['name' => 'require_phone_number'])->row()->value === '1';

        // Validate required fields
        if ( ! isset(
                $customer['first_name'],
                $customer['last_name'],
                $customer['email']
            )
            || ( ! isset($customer['phone_number']) && $phone_number_required))
        {
            throw new Exception('Not all required fields are provided: ' . print_r($customer, TRUE));
        }

        // Validate email address
        if ( ! filter_var($customer['email'], FILTER_VALIDATE_EMAIL))
        {
            throw new Exception('Invalid email address provided: ' . $customer['email']);
        }

        // When inserting a record the phone number must be unique.
        $customer_id = isset($customer['id']) ? $customer['id'] : '';

        $num_rows = $this->db
            ->select('*')
            ->from('users')
            ->join('roles', 'roles.id = users.id_roles', 'inner')
            ->where('roles.slug', DB_SLUG_CUSTOMER)
            ->where('users.phone_number', $customer['phone_number'])
            ->where('users.id !=', $customer_id)
            ->get()
            ->num_rows();

        if ($num_rows > 0)
        {
            throw new Exception('Given phone number belongs to another customer record. '
                . 'Please use a different phone number.');
        }



        return TRUE;
    }

    /**
     * Check if a particular customer record already exists.
     *
     * This method checks whether the given customer already exists in the database. It doesn't search with the id, but
     * with the following fields: "phone_number"
     *
     * @param array $customer Associative array with the customer's data. Each key has the same name with the database
     * fields.
     *
     * @return bool Returns whether the record exists or not.
     *
     * @throws Exception If customer email property is missing.
     */
    public function exists($customer)
    {
        if (empty($customer['phone_number']))
        {
            throw new Exception('Customer\'s phone number is not provided.');
        }

        // This method shouldn't depend on another method of this class.
        $num_rows = $this->db
            ->select('*')
            ->from('users')
            ->join('roles', 'roles.id = users.id_roles', 'inner')
            ->where('users.phone_number', $customer['phone_number'])
            ->where('roles.slug', DB_SLUG_CUSTOMER)
            ->get()->num_rows();

        return $num_rows > 0;
    }

    /**
     * Find the database id of a customer record.
     *
     * The customer data should include the following fields in order to get the unique id from the database: "phone_number"
     *
     * IMPORTANT: The record must already exists in the database, otherwise an exception is raised.
     *
     * @param array $customer Array with the customer data. The keys of the array should have the same names as the
     * database fields.
     *
     * @return int Returns the ID.
     *
     * @throws Exception If customer record does not exist.
     */
    public function find_record_id($customer)
    {
        if (empty($customer['phone_number']))
        {
            throw new Exception('Customer\'s phone number was not provided: '
                . print_r($customer, TRUE));
        }

        // Get customer's role id
        $result = $this->db
            ->select('users.id')
            ->from('users')
            ->join('roles', 'roles.id = users.id_roles', 'inner')
            ->where('users.phone_number', $customer['phone_number'])
            ->where('roles.slug', DB_SLUG_CUSTOMER)
            ->get();

        if ($result->num_rows() == 0)
        {
            throw new Exception('Could not find customer record id.');
        }

        return $result->row()->id;
    }

    /**
     * Insert a new customer record to the database.
     *
     * @param array $customer Associative array with the customer's data. Each key has the same name with the database
     * fields.
     *
     * @return int Returns the id of the new record.
     *
     * @throws Exception If customer record could not be inserted.
     */
    protected function insert($customer)
    {
        // Before inserting the customer we need to get the customer's role id
        // from the database and assign it to the new record as a foreign key.
        $customer_role_id = $this->db
            ->select('id')
            ->from('roles')
            ->where('slug', DB_SLUG_CUSTOMER)
            ->get()->row()->id;

        $customer['id_roles'] = $customer_role_id;

        if ( ! $this->db->insert('users', $customer))
        {
            throw new Exception('Could not insert customer to the database.');
        }

        return (int)$this->db->insert_id();
    }

    /**
     * Update an existing customer record in the database.
     *
     * The customer data argument should already include the record ID in order to process the update operation.
     *
     * @param array $customer Associative array with the customer's data. Each key has the same name with the database
     * fields.
     *
     * @return int Returns the updated record ID.
     *
     * @throws Exception If customer record could not be updated.
     */
    protected function update($customer)
    {
        $this->db->where('id', $customer['id']);

        if ( ! $this->db->update('users', $customer))
        {
            throw new Exception('Could not update customer to the database.');
        }

        return (int)$customer['id'];
    }

    /**
     * Delete an existing customer record from the database.
     *
     * @param int $customer_id The record id to be deleted.
     *
     * @return bool Returns the delete operation result.
     *
     * @throws Exception If $customer_id argument is invalid.
     */
    public function delete($customer_id)
    {
        if ( ! is_numeric($customer_id))
        {
            throw new Exception('Invalid argument type $customer_id: ' . $customer_id);
        }

        $num_rows = $this->db->get_where('users', ['id' => $customer_id])->num_rows();
        if ($num_rows == 0)
        {
            return FALSE;
        }

        return $this->db->delete('users', ['id' => $customer_id]);
    }

    /**
     * Get a specific row from the appointments table.
     *
     * @param int $customer_id The record's id to be returned.
     *
     * @return array Returns an associative array with the selected record's data. Each key has the same name as the
     * database field names.
     *
     * @throws Exception If $customer_id argumnet is invalid.
     */
    public function get_row($customer_id)
    {
        if ( ! is_numeric($customer_id))
        {
            throw new Exception('Invalid argument provided as $customer_id : ' . $customer_id);
        }
        return $this->db->get_where('users', ['id' => $customer_id])->row_array();
    }

    /**
     * Get a specific field value from the database.
     *
     * @param string $field_name The field name of the value to be returned.
     * @param int $customer_id The selected record's id.
     *
     * @return string Returns the records value from the database.
     *
     * @throws Exception If $customer_id argument is invalid.
     * @throws Exception If $field_name argument is invalid.
     * @throws Exception If requested customer record does not exist in the database.
     * @throws Exception If requested field name does not exist in the database.
     */
    public function get_value($field_name, $customer_id)
    {
        if ( ! is_numeric($customer_id))
        {
            throw new Exception('Invalid argument provided as $customer_id: '
                . $customer_id);
        }

        if ( ! is_string($field_name))
        {
            throw new Exception('$field_name argument is not a string: '
                . $field_name);
        }

        if ($this->db->get_where('users', ['id' => $customer_id])->num_rows() == 0)
        {
            throw new Exception('The record with the $customer_id argument '
                . 'does not exist in the database: ' . $customer_id);
        }

        $row_data = $this->db->get_where('users', ['id' => $customer_id])->row_array();

        if ( ! array_key_exists($field_name, $row_data))
        {
            throw new Exception('The given $field_name argument does not exist in the database: '
                . $field_name);
        }

        $customer = $this->db->get_where('users', ['id' => $customer_id])->row_array();

        return $customer[$field_name];
    }

    /**
     * Get all, or specific records from appointment's table.
     *
     * Example:
     *
     * $this->appointments_model->get_batch([$id => $record_id]);
     *
     * @param mixed|null $where
     * @param int|null $limit
     * @param int|null $offset
     * @param mixed|null $order_by
     *
     * @return array Returns the rows from the database.
     */
    public function get_batch($where = NULL, $limit = NULL, $offset = NULL, $order_by = NULL)
    {
        $role_id = $this->get_customers_role_id();

        if ($where !== NULL)
        {
            $this->db->where($where);
        }

        if ($order_by !== NULL)
        {
            $this->db->order_by($order_by);
        }

        return $this->db->get_where('users', ['id_roles' => $role_id], $limit, $offset)->result_array();
    }

    /**
     * Get the customers role id from the database.
     *
     * @return int Returns the role id for the customer records.
     */
    public function get_customers_role_id()
    {
        return $this->db->get_where('roles', ['slug' => DB_SLUG_CUSTOMER])->row()->id;
    }


}
