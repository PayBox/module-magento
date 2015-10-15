<?php

class PB_PaymentPaybox_Block_Adminhtml_System_Config_Form_Field_CallbackUrls
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Heading
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $baseUrl = Mage::getBaseUrl('web');
        return sprintf(
            '<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4 id="%s">%s</h4></td></tr>' .
            '<tr><td colspan="5"><span>' . $this->__("You need to set callback links for your shop in your account on PayBox website to make extension work.") . '</span></td></tr>' .
            '<tr><td class="label"><label>' . $this->__("Result URL") . '</label></td><td class="value">' . $baseUrl . 'pbpaybox/status<p class="note"><span>' . $this->__("POST or GET data transfer method") . '</span></p></td></tr>' .
            '<tr><td class="label"><label>' . $this->__("Success URL") . '</label></td><td class="value">' . $baseUrl . 'pbpaybox/success<p class="note"><span>' . $this->__("POST or GET data transfer method") . '</span></p></td></tr>' .
            '<tr><td class="label"><label>' . $this->__("Failure URL") . '</label></td><td class="value">' . $baseUrl . 'pbpaybox/failure<p class="note"><span>' . $this->__("POST or GET data transfer method") . '</span></p></td></tr>' .
            '<tr><td class="label"><label>' . $this->__("Documentation") . '</label></td><td class="value"><a href="https://github.com/PayBox/module-magento" target="_blank">https://github.com/PayBox/module-magento</a></td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), $element->getLabel()
        );
    }
}
