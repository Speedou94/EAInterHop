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

class Security_library
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    /**
     * Create encrypt keys.
     *
     * Create a pair of private/public keys needed to protect data. Those keys are
     * stored in the user session.
     *
     * @param bool $force Create private/public keys even they already exist
     *
     * @return void
     */

    public function createKeys(bool $force = false): void
    {
        // Current session.
        $session = $this->CI->session;

        // IF keys need to be created or replaced.
        if (($force) || (!$session->has_userdata('keys')))
        {
            // Create private/public keys.
            $keys = openssl_pkey_new(array(
                "digest_alg" => "SHA256",
                "private_key_bits" => 1024,
                "private_key_type" => OPENSSL_KEYTYPE_RSA
            ));

            // Retrieve the PEM form of the keys.
            $publicKeyPEM = openssl_pkey_get_details($keys)['key'];
            openssl_pkey_export($keys, $privateKeyPEM);

            // Keys are created.
            $session->set_userdata('keys', true);
            // Store the private PEM key.
            $session->set_userdata('private_key', $privateKeyPEM);
            // Store the public PEM key.
            $session->set_userdata('public_key', $publicKeyPEM);
        }
    }

    /**
     * Decrypt data using private key.
     *
     * Decrypt data supplied as parameter. Those data are encoded using base64 method. Key need to be create first to
     * allow this function to work properly.
     *
     * @param String $data Data to decrypt, passes as a base64 encoded string.
     *
     * @return String Data decoded.
     */

    public function decrypt(string $data): string
    {
        // Current session.
        $session = $this->CI->session;

        // Result by default.
        $decrypted = "";

        // Restore data format.
        $encrypted = base64_decode($data);

        // Retrieve the private key used to decrypt data.
        $private_key = $session->userdata('private_key');

        // Now decrypt data.
        openssl_private_decrypt($encrypted, $decrypted, $private_key);

        // If errors occur, display them.
        while ($msg = openssl_error_string()) echo $msg . "<br />\n";

        // Return the result.
        return $decrypted;
    }

    /**
     * @param string $data
     *
     * @return void
     */

    public function selfDecrypt(?string &$data) : void
    {
        if (!isset($data)) return;

        $encrypted = $data;

        $data = $this->decrypt($encrypted);
    }

    /**
     * Obtain the current public key.
     *
     * This function will return the current generated public key needed to encrypt data to be further decrypted by the
     * decrypt() function.
     *
     * @return String Key in PEM format.
     */

    public function getPublicKey(): string
    {
        // Create the cryptographic keys if not already done
        $this->createKeys();

        return $this->CI->session->userdata('public_key');
    }
}
