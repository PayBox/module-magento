<?php

class PB_PaymentPaybox_Block_Error extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $storeId = Mage::app()->getStore()->getId();
        $html = '<html><body>';
        $html .= $this->__('An error has occurred during the checkout process. Please, contact the store owner.');
        $html .= '<br><br><a href="' .
            Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . '">' .
            $this->__('Back to the main page') . '</a>';
        $html .= '</body></html>';
        return $html;
    }

}
