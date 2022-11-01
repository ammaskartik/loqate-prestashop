<?php

defined('_PS_VERSION_') or exit;

require_once _PS_ROOT_DIR_ . '/modules/loqate/vendor/autoload.php';

use Loqate\ApiConnector\Client\Capture;

/**
 * Retrieve class
 */
class LoqateRetrieveModuleFrontController extends ModuleFrontControllerCore
{
    /** @var Capture $apiConnector */
    private $apiConnector;

    public function __construct()
    {
        parent::__construct();
        if ($apiKey = Configuration::get('LOQ_API_KEY')) {
            $this->apiConnector = new Capture($apiKey);
        }
    }

    public function initContent()
    {
        if ($this->apiConnector) {
            $addressId = Tools::getValue('address_id');
            $page = ucfirst(Tools::getValue('page'));
            $apiRequestParams = ['Id' => $addressId];

            $result = $this->apiConnector->retrieve($apiRequestParams);

            if (isset($result['error'])) {
                PrestaShopLogger::addLog("Loqate Module - find address: " . $result['message']);
                exit(json_encode(['error' => true, 'message' => 'Error occurred while trying to process your request']));
            }
            if (isset($result[0]['CountryIso2'])) {
                $result[0]['CountryIdPresta'] = Country::getByIso($result[0]['CountryIso2']);
            }
            $result[0]['StateId'] = 'none';
            if (isset($result[0]['ProvinceName']) && Country::containsStates($result[0]['CountryIdPresta'])) {
                $states = State::getStatesByIdCountry($result[0]['CountryIdPresta']);
                foreach ($states as $state) {
                    if (stripos($state['name'], $result[0]['ProvinceName']) !== false) {
                        $result[0]['StateId'] = $state['id_state'];
                        break;
                    }
                }
            }

            $this->context->cookie->{'loqate' . $page} = true;
            $this->context->cookie->{'loqateCity' . $page} = $result[0]['City'];
            $this->context->cookie->{'loqateAddress1' . $page} = $result[0]['Line1'];
            $this->context->cookie->{'loqateAddress2' . $page} = $result[0]['Line2'];
            $this->context->cookie->{'loqateStreet' . $page} = $result[0]['Street'];
            $this->context->cookie->{'loqatePostcode' . $page} = $result[0]['PostalCode'];
            $this->context->cookie->{'loqateCountry' . $page} = $result[0]['CountryIso2'];
            $this->context->cookie->{'loqateState' . $page} = $result[0]['StateId'];
            exit(json_encode($result));
        } else {
            exit(json_encode(['error' => true, 'message' => 'Object could not be initialized']));
        }
    }
}
