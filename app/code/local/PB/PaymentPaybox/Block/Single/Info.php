<?php

class PB_PaymentPaybox_Block_Single_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('pb_paymentpaybox/single/info.phtml');
    }
}