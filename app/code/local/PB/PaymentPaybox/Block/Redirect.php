<?php

class PB_PaymentPaybox_Block_Redirect extends Mage_Core_Block_Abstract
{
    protected $_postData;

    protected function _toHtml()
    {
        $html = '<html><body>';
        $html .= $this->__('You will be redirected to payment form in a few seconds.');
        $html .= '<form method="get" action="' . $this->getGateUrl() . '" id="gate_post_form">';
        foreach ($this->_postData as $key => $value) {
            $html .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
        }
        print '<input type="submit" value="' . $this->__("Click here, if not redirected for 30 seconds") . '">';
        $html .= '</form><script type="text/javascript">document.getElementById("gate_post_form").submit();</script>';
        $html .= '</body></html>';
        return $html;
    }

    public function setPostData($data)
    {
        $this->_postData = $data;
        return $this;
    }
}
