<?php
/**
 * 2022 Loqate
 *
 * @author    Loqate
 * @copyright 2022 Loqate
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_ROOT_DIR_ . '/modules/loqate/vendor/autoload.php';

class Loqate extends Module
{
    /**
     * Hooks array
     *
     * @var array
     */
    private $hooks = [
        'displayHeader',
        'actionAdminControllerSetMedia',
        'actionValidateCustomerAddressForm',
        'actionSubmitCustomerAddressForm'
    ];

    /**
     * Hold language array
     *
     * @var mixed[] $languages
     */
    protected $languages = array();

    private $_html = '';

    public function __construct()
    {
        $this->name = 'loqate';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Loqate';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7.7.1', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Loqate');
        $this->description = $this->l('Loqate address lookup and validations.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    /**
     * Install
     *
     * @return bool
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        if (!parent::install()) {
            $this->_errors[] = sprintf($this->l('Could not install %s'), 'module');
            return false;
        }
        $return = true;
        foreach ($this->hooks as $hook) {
            $return &= $this->registerHook($hook);
        }
        return $return;
    }

    /**
     * Uninstall
     *
     * @return bool
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            $this->_errors[] = sprintf($this->l('Could not uninstall %s'), 'module');
            return false;
        }

        return true;
    }

    /**
     * Get content for module settings
     *
     * @return mixed
     */
    public function getContent()
    {
        $this->_html = '';
        $this->processConfiguration();
        $this->_html .= $this->renderForm();
        return $this->_html;
    }

    /**
     * Process configuration
     */
    public function processConfiguration()
    {
        if (Tools::isSubmit('submitLoqateSettings')) {
            Configuration::updateValue('LOQ_API_KEY', Tools::getValue('LOQ_API_KEY'));
            Configuration::updateValue('LOQ_CAPTURE_CUSTOMER_ACCOUNT', Tools::getValue('LOQ_CAPTURE_CUSTOMER_ACCOUNT'));
            Configuration::updateValue('LOQ_CAPTURE_CHECKOUT', Tools::getValue('LOQ_CAPTURE_CHECKOUT'));
            Configuration::updateValue('LOQ_CAPTURE_ADDRESS_ADMIN', Tools::getValue('LOQ_CAPTURE_ADDRESS_ADMIN'));
            Configuration::updateValue('LOQ_ADDR_QI', Tools::getValue('LOQ_ADDR_QI'));
            Configuration::updateValue('LOQ_ADDR_VERIFICATION_CUSTOMER_ACCOUNT', Tools::getValue('LOQ_ADDR_VERIFICATION_CUSTOMER_ACCOUNT'));
            Configuration::updateValue('LOQ_ADDR_VERIFICATION_CHECKOUT', Tools::getValue('LOQ_ADDR_VERIFICATION_CHECKOUT'));
            Configuration::updateValue('LOQ_ADDR_VERIFICATION_ADDRESS_ADMIN', Tools::getValue('LOQ_ADDR_VERIFICATION_ADDRESS_ADMIN'));
            Configuration::updateValue('LOQ_ADDR_VERIFICATION_ADDRESS_IMPORT', Tools::getValue('LOQ_ADDR_VERIFICATION_ADDRESS_IMPORT'));
            Configuration::updateValue('LOQ_EMAIL_VERIFICATION_BLOCK_SUBMIT', Tools::getValue('LOQ_EMAIL_VERIFICATION_BLOCK_SUBMIT'));
            Configuration::updateValue('LOQ_EMAIL_VERIFICATION_CUSTOMER_ACCOUNT', Tools::getValue('LOQ_EMAIL_VERIFICATION_CUSTOMER_ACCOUNT'));
            Configuration::updateValue('LOQ_EMAIL_VERIFICATION_CHECKOUT', Tools::getValue('LOQ_EMAIL_VERIFICATION_CHECKOUT'));
            Configuration::updateValue('LOQ_EMAIL_VERIFICATION_REGISTER', Tools::getValue('LOQ_EMAIL_VERIFICATION_REGISTER'));
            Configuration::updateValue('LOQ_EMAIL_VERIFICATION_CUSTOMER_ADMIN', Tools::getValue('LOQ_EMAIL_VERIFICATION_CUSTOMER_ADMIN'));
            Configuration::updateValue('LOQ_PHONE_VERIFICATION_BLOCK_SUBMIT', Tools::getValue('LOQ_PHONE_VERIFICATION_BLOCK_SUBMIT'));
            Configuration::updateValue('LOQ_PHONE_VERIFICATION_CUSTOMER_ACCOUNT', Tools::getValue('LOQ_PHONE_VERIFICATION_CUSTOMER_ACCOUNT'));
            Configuration::updateValue('LOQ_PHONE_VERIFICATION_CHECKOUT', Tools::getValue('LOQ_PHONE_VERIFICATION_CHECKOUT'));
            Configuration::updateValue('LOQ_PHONE_VERIFICATION_ADDRESS_ADMIN', Tools::getValue('LOQ_PHONE_VERIFICATION_ADDRESS_ADMIN'));

            $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
        }
    }

    /**
     * @return mixed
     */
    public function renderForm()
    {
        $fields_form1 = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Main settings'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('API Key'),
                        'name' => 'LOQ_API_KEY',
                        'desc' => $this->l('You can find it in "Your services" section of your account on loqate.com'),
                        'col' => '4',
                        'class' => 'advanced_index_off',
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ]
            ]
        ];

        $fields_form2 = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Address Capture'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Customer Account'),
                        'name' => 'LOQ_CAPTURE_CUSTOMER_ACCOUNT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_CAPTURE_CUSTOMER_ACCOUNT_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_CAPTURE_CUSTOMER_ACCOUNT_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ]
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Checkout'),
                        'name' => 'LOQ_CAPTURE_CHECKOUT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_CAPTURE_CHECKOUT_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_CAPTURE_CHECKOUT_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Customer Address (ADMIN)'),
                        'name' => 'LOQ_CAPTURE_ADDRESS_ADMIN',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_CAPTURE_ADDRESS_ADMIN_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_CAPTURE_ADDRESS_ADMIN_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ]
            ]
        ];

        $fields_form3 = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Address Verification'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Address Quality Index'),
                        'name' => 'LOQ_ADDR_QI',
                        'default_value' => 'Average',
                        'options' => [
                            'query' => [
                                ['name' => 'Excellent', 'id' => 'A'],
                                ['name' => 'Good', 'id' => 'B'],
                                ['name' => 'Average', 'id' => 'C'],
                                ['name' => 'Poor', 'id' => 'D'],
                                ['name' => 'Bad', 'id' => 'E'],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('The Address Quality Index (AQI) is used to indicate the quality of an address, e.g., Excellent quality,') .
                            $this->l('Good quality, Average quality, Poor quality, and Bad quality') . '<br/>',
                        'class' => 'advanced_search_off',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Customer Account'),
                        'name' => 'LOQ_ADDR_VERIFICATION_CUSTOMER_ACCOUNT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_ADDR_VERIFICATION_CUSTOMER_ACCOUNT_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_ADDR_VERIFICATION_CUSTOMER_ACCOUNT_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ]
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Checkout'),
                        'name' => 'LOQ_ADDR_VERIFICATION_CHECKOUT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_ADDR_VERIFICATION_CHECKOUT_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_ADDR_VERIFICATION_CHECKOUT_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Customer Address (ADMIN)'),
                        'name' => 'LOQ_ADDR_VERIFICATION_ADDRESS_ADMIN',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_ADDR_VERIFICATION_ADDRESS_ADMIN_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_ADDR_VERIFICATION_ADDRESS_ADMIN_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Customer Address Import (ADMIN)'),
                        'name' => 'LOQ_ADDR_VERIFICATION_ADDRESS_IMPORT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_ADDR_VERIFICATION_ADDRESS_IMPORT_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_ADDR_VERIFICATION_ADDRESS_IMPORT_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ]
            ]
        ];

        $fields_form4 = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Email Validation'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Prevent submit on invalid Email address'),
                        'name' => 'LOQ_EMAIL_VERIFICATION_BLOCK_SUBMIT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_EMAIL_VERIFICATION_BLOCK_SUBMIT_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_EMAIL_VERIFICATION_BLOCK_SUBMIT_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ]
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Customer Account'),
                        'name' => 'LOQ_EMAIL_VERIFICATION_CUSTOMER_ACCOUNT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_EMAIL_VERIFICATION_CUSTOMER_ACCOUNT_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_EMAIL_VERIFICATION_CUSTOMER_ACCOUNT_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ]
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Checkout'),
                        'name' => 'LOQ_EMAIL_VERIFICATION_CHECKOUT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_EMAIL_VERIFICATION_CHECKOUT_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_EMAIL_VERIFICATION_CHECKOUT_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Register'),
                        'name' => 'LOQ_EMAIL_VERIFICATION_REGISTER',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_EMAIL_VERIFICATION_REGISTER_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_EMAIL_VERIFICATION_REGISTER_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Customer Account (ADMIN)'),
                        'name' => 'LOQ_EMAIL_VERIFICATION_CUSTOMER_ADMIN',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_EMAIL_VERIFICATION_CUSTOMER_ADMIN_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_EMAIL_VERIFICATION_CUSTOMER_ADMIN_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ]
            ]
        ];

        $fields_form5 = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Phone Validation'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Prevent submit on invalid phone number'),
                        'name' => 'LOQ_PHONE_VERIFICATION_BLOCK_SUBMIT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_PHONE_VERIFICATION_BLOCK_SUBMIT_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_PHONE_VERIFICATION_BLOCK_SUBMIT_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ]
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Customer Account'),
                        'name' => 'LOQ_PHONE_VERIFICATION_CUSTOMER_ACCOUNT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_PHONE_VERIFICATION_CUSTOMER_ACCOUNT_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_PHONE_VERIFICATION_CUSTOMER_ACCOUNT_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ]
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Checkout'),
                        'name' => 'LOQ_PHONE_VERIFICATION_CHECKOUT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_PHONE_VERIFICATION_CHECKOUT_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_PHONE_VERIFICATION_CHECKOUT_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable on Customer Account (ADMIN)'),
                        'name' => 'LOQ_PHONE_VERIFICATION_ADDRESS_ADMIN',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'LOQ_PHONE_VERIFICATION_ADDRESS_ADMIN_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'LOQ_PHONE_VERIFICATION_ADDRESS_ADMIN_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ]
            ]
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitLoqateSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name .
            '&tab_module=' . $this->tab .
            '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        ];

        return $helper->generateForm(
            [$fields_form1, $fields_form2, $fields_form3, $fields_form4, $fields_form5]
        );
    }

    /**
     * @return array
     */
    public function getConfigFieldsValues()
    {
        return [
            'LOQ_API_KEY' => Tools::getValue(
                'LOQ_API_KEY',
                Configuration::get('LOQ_API_KEY')
            ),
            'LOQ_CAPTURE_CUSTOMER_ACCOUNT' => Tools::getValue(
                'LOQ_CAPTURE_CUSTOMER_ACCOUNT',
                Configuration::get('LOQ_CAPTURE_CUSTOMER_ACCOUNT')
            ),
            'LOQ_CAPTURE_CHECKOUT' => Tools::getValue(
                'LOQ_CAPTURE_CHECKOUT',
                Configuration::get('LOQ_CAPTURE_CHECKOUT')
            ),
            'LOQ_CAPTURE_ADDRESS_ADMIN' => Tools::getValue(
                'LOQ_CAPTURE_ADDRESS_ADMIN',
                Configuration::get('LOQ_CAPTURE_ADDRESS_ADMIN')
            ),
            'LOQ_ADDR_QI' => Tools::getValue(
                'LOQ_ADDR_QI',
                Configuration::get('LOQ_ADDR_QI')
            ),
            'LOQ_ADDR_VERIFICATION_CUSTOMER_ACCOUNT' => Tools::getValue(
                'LOQ_ADDR_VERIFICATION_CUSTOMER_ACCOUNT',
                Configuration::get('LOQ_ADDR_VERIFICATION_CUSTOMER_ACCOUNT')
            ),
            'LOQ_ADDR_VERIFICATION_CHECKOUT' => Tools::getValue(
                'LOQ_ADDR_VERIFICATION_CHECKOUT',
                Configuration::get('LOQ_ADDR_VERIFICATION_CHECKOUT')
            ),
            'LOQ_ADDR_VERIFICATION_ADDRESS_ADMIN' => Tools::getValue(
                'LOQ_ADDR_VERIFICATION_ADDRESS_ADMIN',
                Configuration::get('LOQ_ADDR_VERIFICATION_ADDRESS_ADMIN')
            ),
            'LOQ_ADDR_VERIFICATION_ADDRESS_IMPORT' => Tools::getValue(
                'LOQ_ADDR_VERIFICATION_ADDRESS_IMPORT',
                Configuration::get('LOQ_ADDR_VERIFICATION_ADDRESS_IMPORT')
            ),
            'LOQ_EMAIL_VERIFICATION_BLOCK_SUBMIT' => Tools::getValue(
                'LOQ_EMAIL_VERIFICATION_BLOCK_SUBMIT',
                Configuration::get('LOQ_EMAIL_VERIFICATION_BLOCK_SUBMIT')
            ),
            'LOQ_EMAIL_VERIFICATION_CUSTOMER_ACCOUNT' => Tools::getValue(
                'LOQ_EMAIL_VERIFICATION_CUSTOMER_ACCOUNT',
                Configuration::get('LOQ_EMAIL_VERIFICATION_CUSTOMER_ACCOUNT')
            ),
            'LOQ_EMAIL_VERIFICATION_CHECKOUT' => Tools::getValue(
                'LOQ_EMAIL_VERIFICATION_CHECKOUT',
                Configuration::get('LOQ_EMAIL_VERIFICATION_CHECKOUT')
            ),
            'LOQ_EMAIL_VERIFICATION_REGISTER' => Tools::getValue(
                'LOQ_EMAIL_VERIFICATION_REGISTER',
                Configuration::get('LOQ_EMAIL_VERIFICATION_REGISTER')
            ),
            'LOQ_EMAIL_VERIFICATION_CUSTOMER_ADMIN' => Tools::getValue(
                'LOQ_EMAIL_VERIFICATION_CUSTOMER_ADMIN',
                Configuration::get('LOQ_EMAIL_VERIFICATION_CUSTOMER_ADMIN')
            ),
            'LOQ_PHONE_VERIFICATION_BLOCK_SUBMIT' => Tools::getValue(
                'LOQ_PHONE_VERIFICATION_BLOCK_SUBMIT',
                Configuration::get('LOQ_PHONE_VERIFICATION_BLOCK_SUBMIT')
            ),
            'LOQ_PHONE_VERIFICATION_CUSTOMER_ACCOUNT' => Tools::getValue(
                'LOQ_PHONE_VERIFICATION_CUSTOMER_ACCOUNT',
                Configuration::get('LOQ_PHONE_VERIFICATION_CUSTOMER_ACCOUNT')
            ),
            'LOQ_PHONE_VERIFICATION_CHECKOUT' => Tools::getValue(
                'LOQ_PHONE_VERIFICATION_CHECKOUT',
                Configuration::get('LOQ_PHONE_VERIFICATION_CHECKOUT')
            ),
            'LOQ_PHONE_VERIFICATION_ADDRESS_ADMIN' => Tools::getValue(
                'LOQ_PHONE_VERIFICATION_ADDRESS_ADMIN',
                Configuration::get('LOQ_PHONE_VERIFICATION_ADDRESS_ADMIN')
            ),
        ];
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->registerJavascript(
            'modules-' . $this->name . '-loqate',
            'modules/' . $this->name . '/views/js/front/loqate.js',
            array('position' => 'bottom', 'priority' => 150)
        );
        $this->context->controller->registerStylesheet(
            'modules-' . $this->name . '-loqate',
            'modules/' . $this->name . '/views/css/loqate.css',
            array('media' => 'all', 'priority' => 150)
        );
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue('controller') == 'AdminAddresses' ||
            Tools::getValue('controller') == 'AdminCustomers') {
            $this->context->controller->addJS(
                _MODULE_DIR_ . $this->name . '/views/js/front/loqate.js',
            );
            $this->context->controller->addCSS(
                _MODULE_DIR_ . $this->name . '/views/css/loqate.css',
            );
            Media::addJsDef([
                'module_dir' => _MODULE_DIR_,
                'is_admin' => true,
                'img_dir' => _PS_IMG_,
            ]);
        }
    }

    public function hookActionValidateCustomerAddressForm($params)
    {
        $validator = new Validator();
        $addressForm = $params['form'];
        $page = '';

        $address['country'] = Country::getIsoById($addressForm->getValue('id_country'));
        $address['address1'] = $addressForm->getValue('address1');
        $address['address2'] = $addressForm->getValue('address2');
        $address['city'] = $addressForm->getValue('city');
        $address['postcode'] = $addressForm->getValue('postcode');
        $address['state'] = $addressForm->getValue('id_state');
        $requireVerify = false;

        if (Tools::getValue('controller') == 'address') {
            $page = 'Address';
        }
        if ($this->context->controller instanceof OrderController) {
            $page = 'Checkout';
        }

        if (($page === 'Checkout' && Configuration::get('LOQ_ADDR_VERIFICATION_CHECKOUT')) ||
            ($page === 'Address' && Configuration::get('LOQ_ADDR_VERIFICATION_CUSTOMER_ACCOUNT'))
        ) {
            //Verify address if config is enabled and the address was captured and edited, or not captured
            $addrCaptured = $this->context->cookie->{'loqate' . $page};
            if ($addrCaptured) {
                if ($address['postcode'] != $this->context->cookie->{'loqatePostcode' . $page} ||
                    $address['city'] != $this->context->cookie->{'loqateCity' . $page} ||
                    $address['address1'] != $this->context->cookie->{'loqateAddress1' . $page} ||
                    $address['state'] != $this->context->cookie->{'loqateState' . $page} ||
                    $address['country'] != $this->context->cookie->{'loqateCountry' . $page}
                ) {
                    $requireVerify = true;
                }
            } else {
                $requireVerify = true;
            }

            if ($requireVerify) {
                $response = $validator->verifyAddress(['Addresses' => $address]);
                if (!$response['AQI']) {
                    if ($addrCaptured) {
                        if ($address['city'] != $this->context->cookie->{'loqateCity' . $page}) {
                            $addressForm->getField('city')->addError($this->l('Invalid city'));
                        } elseif ($address['postcode'] != $this->context->cookie->{'loqatePostcode' . $page} &&
                            !count($addressForm->getErrors()['postcode'])
                        ) {
                            $addressForm->getField('postcode')->addError($this->l('Invalid postcode'));
                        } else {
                            $addressForm->getField('address1')->addError($this->l('Invalid address'));
                        }
                    } else {
                        $addressForm->getField('address1')->addError($this->l('Invalid address'));
                    }
                }
            }
        }

        if (($page === 'Checkout' && Configuration::get('LOQ_PHONE_VERIFICATION_CHECKOUT')) ||
            ($page === 'Address' && Configuration::get('LOQ_PHONE_VERIFICATION_CUSTOMER_ACCOUNT'))
        ) {
            if (!empty($addressForm->getValue('phone'))) {
                if (Configuration::get('LOQ_PHONE_VERIFICATION_BLOCK_SUBMIT')) {
                    if (!$validator->verifyPhoneNumber($addressForm->getValue('phone'), $address['country'])) {
                        $addressForm->getField('phone')->addError(
                            $this->l('The phone number is invalid.')
                        );
                    }
                } elseif (!$this->context->cookie->{'loqatePhoneVerified' . $page} ||
                    ($this->context->cookie->{'loqatePhoneVerified' . $page} &&
                        $this->context->cookie->{'loqatePhone' . $page} != $addressForm->getValue('phone'))
                ) {
                    //Verify phone if it has not been verified, or phone has been verified and submitted again, or
                    //phone has been verified but edited and submitted again
                    if (!$validator->verifyPhoneNumber($addressForm->getValue('phone'), $address['country'])) {
                        $this->context->cookie->{'loqatePhoneVerified' . $page} = true;
                        $this->context->cookie->{'loqatePhone' . $page} = $addressForm->getValue('phone');
                        $addressForm->getField('phone')->addError(
                            $this->l('The phone number could not be verified. Submit again to use this phone number.')
                        );
                    }
                }
            }
        }
    }

    public function hookActionSubmitCustomerAddressForm()
    {
        $page = '';
        if (Tools::getValue('controller') == 'address') {
            $page = 'Address';
        }
        if ($this->context->controller instanceof OrderController) {
            $page = 'Checkout';
        }
        if ($page && $this->context->cookie->{'loqate' . $page}) {
            $this->unsetCookies($page);
        }
    }

    public function verifyEmail($email)
    {
        return (new Validator())->verifyEmail($email);
    }

    public function ajaxAdminVerifyAddress()
    {
        $validator = new Validator();
        $errors = [];
        $addrCaptured = false;
        $address['address1'] = Tools::getValue('address1');
        $address['address2'] = Tools::getValue('address2');
        $address['postcode'] = Tools::getValue('postcode');
        $address['state'] = Tools::getValue('state');
        $address['city'] = Tools::getValue('city');
        $address['country'] = Tools::getValue('country');
        $address['page'] = Tools::getValue('page');// page = admin
        $address['country'] = Country::getIsoById($address['country']);
        $address['phone'] = Tools::getValue('phone');

        if (Configuration::get('LOQ_ADDR_VERIFICATION_ADDRESS_ADMIN')) {
            $requireVerify = false;
            $addrCaptured = $this->context->cookie->loqateAdmin;

            if ($addrCaptured) {
                if ($address['postcode'] != $this->context->cookie->loqatePostcodeAdmin ||
                    $address['city'] != $this->context->cookie->loqateCityAdmin ||
                    $address['address1'] != $this->context->cookie->loqateAddress1Admin ||
                    $address['state'] != $this->context->cookie->loqateStateAdmin ||
                    $address['country'] != $this->context->cookie->loqateCountryAdmin
                ) {
                    $requireVerify = true;
                }
            } else {
                $requireVerify = true;
            }

            if ($requireVerify) {
                $response = $validator->verifyAddress(['Addresses' => $address]);
                if (!$response['AQI']) {
                    if ($addrCaptured) {
                        if ($address['city'] != $this->context->cookie->loqateCityAdmin) {
                            $errors[] = $this->l('Invalid city');
                        } elseif ($address['postcode'] != $this->context->cookie->loqatePostcodeAdmin) {
                            $errors[] = $this->l('Invalid postcode');
                        } else {
                            $errors[] = $this->l('Invalid address');
                        }
                    } else {
                        $errors[] = $this->l('Invalid address');
                    }
                }
            }
        }

        $phoneWarning = false;
        $warnings = [];
        if (!empty($address['phone']) &&
            Configuration::get('LOQ_PHONE_VERIFICATION_ADDRESS_ADMIN')
        ) {
            if (Configuration::get('LOQ_PHONE_VERIFICATION_BLOCK_SUBMIT')) {
                if (!$validator->verifyPhoneNumber($address['phone'], $address['country'])) {
                    $errors[] = $this->l('The phone number is invalid.');
                }
            } elseif (!$this->context->cookie->loqatePhoneVerifiedAdmin ||
                ($this->context->cookie->loqatePhoneVerifiedAdmin &&
                    $this->context->cookie->loqatePhoneAdmin != $address['phone'])
            ) {
                //Verify phone if it has not been verified, or phone has been verified and submitted again, or
                //phone has been verified but edited and submitted again
                if (!$validator->verifyPhoneNumber($address['phone'], $address['country'])) {
                    $warnings[] = $this->l('The phone number could not be verified. Submit again to use this phone number.');
                    $this->context->cookie->loqatePhoneVerifiedAdmin = true;
                    $this->context->cookie->loqatePhoneAdmin = $address['phone'];
                    $phoneWarning = true;
                }
            }
        }

        if (count($errors) || count($warnings)) {
            return [
                'error' => true,
                'errors' => $errors,
                'phoneWarning' => $phoneWarning,
                'warnings' => $warnings
            ];
        }

        if ($addrCaptured || $this->context->cookie->loqatePhoneVerifiedAdmin) {
            $this->unsetCookies('Admin');
        }

        return ['valid' => true];
    }

    public function ajaxAdminVerifyEmail()
    {
        $email = Tools::getValue('email');
        $errors = [];
        $warnings = [];
        if (Configuration::get('LOQ_EMAIL_VERIFICATION_CUSTOMER_ADMIN')) {
            if (Configuration::get('LOQ_EMAIL_VERIFICATION_BLOCK_SUBMIT')) {
                if (!$this->verifyEmail($email)) {
                    $errors[] = $this->l('The email address is invalid');
                    return [
                        'errors' => $errors,
                        'error' => true
                    ];
                }
            } elseif (!$this->context->cookie->loqateAdminEmailVerified &&
                !$this->verifyEmail($email)
            ) {
                $this->context->cookie->loqateAdminEmailVerified = true;
                $warnings[] = $this->l('The email could not be verified. Submit again to use this email address.');
                return [
                    'warnings' => $warnings,
                    'warning' => true
                ];
            }
        }
        unset($this->context->cookie->loqateAdminEmailVerified);

        return ['valid' => true];
    }

    public function unsetCookies($page)
    {
        unset(
            $this->context->cookie->{'loqate' . $page},
            $this->context->cookie->{'loqateCity' . $page},
            $this->context->cookie->{'loqateAddress1' . $page},
            $this->context->cookie->{'loqateAddress2' . $page},
            $this->context->cookie->{'loqateStreet' . $page},
            $this->context->cookie->{'loqatePostcode' . $page},
            $this->context->cookie->{'loqateCountry' . $page},
            $this->context->cookie->{'loqateState' . $page},
            $this->context->cookie->{'loqatePhone' . $page},
            $this->context->cookie->{'loqatePhoneVerified' . $page}
        );
    }

    public function verifyAddress($addresses)
    {
        $validator = new Validator();
        $result = $validator->verifyAddress($addresses);
        if (!is_array($result['AQI'])) {
            return [$result['AQI']];
        }
        return $result['AQI'];
    }
}
