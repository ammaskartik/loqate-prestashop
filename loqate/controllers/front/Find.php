<?php

defined('_PS_VERSION_') or exit;

require_once _PS_ROOT_DIR_ . '/modules/loqate/vendor/autoload.php';

use Loqate\ApiConnector\Client\Capture;

/**
 * Find class
 */
class LoqateFindModuleFrontController extends ModuleFrontControllerCore
{
    /** @var Capture $apiConnector */
    private $apiConnector;
    private $enabled;
    private $version;

    public function __construct()
    {
        parent::__construct();
        if ($apiKey = Configuration::get('LOQ_API_KEY')) {
            $this->apiConnector = new Capture($apiKey);
        }
        $this->enabled = false;
        $page = Tools::getValue('page');
        if (($page == 'checkout' && Configuration::get('LOQ_CAPTURE_CHECKOUT')) ||
            ($page == 'address' && Configuration::get('LOQ_CAPTURE_CUSTOMER_ACCOUNT')) ||
            ($page == 'adminaddresses' && Configuration::get('LOQ_CAPTURE_ADDRESS_ADMIN'))) {
            $this->enabled = true;
        }
        $module = Module::getInstanceByName('Loqate');
        $this->version = 'Prestashop_v' . $module->version;
    }

    public function initContent()
    {
        if ($this->enabled) {
            if ($this->apiConnector) {
                $searchText = Tools::getValue('text');
                $countries = json_decode(Tools::getValue('countries'));
                $countriesParam = '';

                if ($countries) {
                    foreach ($countries as $country) {
                        $countriesParam .= Country::getIsoById($country) . ',';
                    }
                }

                $apiRequestParams = [
                    'Text' => $searchText,
                    'Countries' => $countriesParam,
                    'source' => $this->version
                ];

                $result = $this->apiConnector->find($apiRequestParams);

                if (isset($result['error'])) {
                    PrestaShopLogger::addLog("Loqate Module - find address: " . $result['message']);
                    exit(json_encode(['error' => true, 'message' => 'Error occurred while trying to process your request']));
                }
                exit(json_encode($result));
            } else {
                exit(json_encode(['error' => true, 'message' => 'Object could not be initialized']));
            }
        }
        exit(json_encode(['error' => true, 'message' => 'Service disabled']));
    }
}
