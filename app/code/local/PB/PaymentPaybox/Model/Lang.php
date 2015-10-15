<?php

class PB_PaymentPaybox_Model_Lang extends Mage_Core_Model_Abstract
{

    public function toOptionArray()
    {
        $data = array(
            array('value' => "en", 'label' => Mage::helper('pbpaymentpaybox')->__('English')),
            array('value' => "ru", 'label' => Mage::helper('pbpaymentpaybox')->__('Russian'))
        );
        return $data;
    }

}
