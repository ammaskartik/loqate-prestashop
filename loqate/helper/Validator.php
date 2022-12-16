<?php

defined('_PS_VERSION_') or exit;

require_once _PS_ROOT_DIR_ . '/modules/loqate/vendor/autoload.php';

use Loqate\ApiConnector\Client\Verify;

class Validator
{
    const ADDRESS_MAPPING = [
        'address1' => 'Address1',
        'address2' => 'Address2',
        'postcode' => 'PostalCode',
        'city' => 'Locality',
        'country' => 'Country',
        'state' => 'Address4'
    ];

    /** @var Verify $apiConnector */
    private $apiConnector;

    private $version;

    public function __construct()
    {
        if ($apiKey = Configuration::get('LOQ_API_KEY')) {
            $this->apiConnector = new Verify($apiKey);
        }
        $module = Module::getInstanceByName('Loqate');
        $this->version = 'Prestashop_v' . $module->version;
    }

    /**
     * Verify email address
     *
     * @param $emailAddress
     * @return bool
     */
    public function verifyEmail($emailAddress)
    {
        $response = $this->apiConnector->verifyEmail(['Email' => $emailAddress, 'source' => $this->version]);

        if (isset($response['error'])) {
            PrestaShopLogger::addLog("Loqate Module - verify email: " . $response['message']);

            return false;
        }

        return $response;
    }

    /**
     * Verify phone number
     *
     * @param $phoneNumber
     * @return bool
     */
    public function verifyPhoneNumber($phoneNumber, $countryIso)
    {
        $response = $this->apiConnector->verifyPhone([
            'Phone' => $phoneNumber,
            'Country' => $countryIso,
            'source' => $this->version
        ]);

        if (isset($response['error'])) {
            PrestaShopLogger::addLog("Loqate Module - verify phone number: " . $response['message']);

            return false;
        }

        return $response;
    }

    /**
     * Verify addresses using Loqate API
     *
     * @param $addresses
     * @return array|bool
     */
    public function verifyAddress($addresses)
    {
        $requestArray = $this->parseAddresses($addresses);
        $response = $this->apiConnector->verifyAddress(['Addresses' => $requestArray, 'source' => $this->version]);

        if (isset($response['error'])) {
            PrestaShopLogger::addLog("Loqate Module - verify address: " . $response['message']);
            return false;
        }

        //multiple addresses checked
        if (count($response) > 1) {
            $qualityIndex = [];
            foreach ($response as $address) {
                $qualityIndex[] = $this->checkQualityIndex($address[0]['AQI']);
            }
        } else if (isset($response[0][0]) && is_array($response[0][0])) {
            $qualityIndex = $this->checkQualityIndex($response[0][0]['AQI']);
        } else {
            $qualityIndex = $this->checkQualityIndex($response[0]['AQI']);
        }
        return [
            'response' => $response,
            'AQI' => $qualityIndex
        ];
    }

    /**
     * Parse addresses and return expected format for verify request
     *
     * @param $addresses
     * @return array
     */
    private function parseAddresses($addresses)
    {
        $result = [];
        foreach ($addresses as $address) {
            $formattedAddress = [];
            foreach (self::ADDRESS_MAPPING as $key => $value) {
                if (isset($address[$key])) {
                    $formattedAddress[$value] = $address[$key];
                }
            }
            $result[] = $formattedAddress;
        }

        return $result;
    }

    /**
     * Check if response quality index matches the quality customer has set
     *
     * @param $qualityIndex
     * @return bool
     */
    private function checkQualityIndex($qualityIndex)
    {
        $configIndex = Configuration::get('LOQ_ADDR_QI');
        return $qualityIndex <= $configIndex;
    }
}
