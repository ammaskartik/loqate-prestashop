<?php

class CustomerForm extends CustomerFormCore
{

    public function validate()
    {
        if (Module::isEnabled('loqate')) {
            if ((Tools::getValue('controller') == 'authentication' && Configuration::get('LOQ_EMAIL_VERIFICATION_REGISTER')) ||
                (Tools::getValue('controller') == 'identity' && Configuration::get('LOQ_EMAIL_VERIFICATION_CUSTOMER_ACCOUNT')) ||
                (Tools::getValue('controller') == 'order' && Configuration::get('LOQ_EMAIL_VERIFICATION_CHECKOUT'))
            ) {
                $emailField = $this->getField('email');
                $loqate = Module::getInstanceByName('loqate');

                //If the customer tries a second time with the same email which fails validation he can proceed.
                // We do not block the submit unless LOQ_EMAIL_VERIFICATION_BLOCK_SUBMIT is true

                if (Configuration::get('LOQ_EMAIL_VERIFICATION_BLOCK_SUBMIT')) {
                    if (!$loqate->verifyEmail($emailField->getValue())) {
                        $emailField->addError($loqate->l('Invalid email address'));
                    }
                } else if (Context::getcontext()->cookie->loqateEmailVerified &&
                    ($emailField->getValue() == Context::getcontext()->cookie->loqateEmailVerifiedValue)
                ) {
                    unset(Context::getcontext()->cookie->loqateEmailVerified);
                    unset(Context::getcontext()->cookie->loqateEmailVerifiedValue);
                } else if (!$loqate->verifyEmail($emailField->getValue())) {
                    Context::getcontext()->cookie->loqateEmailVerified = true;
                    Context::getcontext()->cookie->loqateEmailVerifiedValue = $emailField->getValue();
                    $emailField->addError($loqate->l('The email could not be verified. Submit again to use this email address.'));
                }
            }
        }

        return parent::validate();
    }
}
