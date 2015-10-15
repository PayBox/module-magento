<?php

class PB_PaymentPaybox_Block_Single_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('pb_paymentpaybox/single/form.phtml');
    }
}