<?php

class PB_PaymentPaybox_Model_Paysystem extends Mage_Core_Model_Abstract
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var $helper PB_PaymentPaybox_Helper_Data*/
        $helper = Mage::helper('pbpaymentpaybox');
        $data = array(
            array('value' => "", 'label' => $helper->__('Any')),
            array('value' => "BANKOCEAN2R", 'label' => $helper->__("Интернет-Банк Океан")),
            array('value' => "OceanBankOceanR", 'label' => $helper->__("Интернет-Банк Океан (platezh.ru)")),
            array('value' => "Qiwi29OceanR", 'label' => $helper->__("Платежи через терминалы QIWI")),
            array('value' => "YandexMerchantR", 'label' => $helper->__("Яндекс.Деньги")),
            array('value' => "WMRM", 'label' => $helper->__("Webmoney WMR (Рубли)")),
            array('value' => "WMZM", 'label' => $helper->__("Webmoney WMZ (Доллары)")),
            array('value' => "WMEM", 'label' => $helper->__("Webmoney WME (Евро)")),
            array('value' => "WMUM", 'label' => $helper->__("Webmoney WMU (Украинские гривны)")),
            array('value' => "WMBM", 'label' => $helper->__("Webmoney WMB (Белорусские рубли)")),
            array('value' => "WMGM", 'label' => $helper->__("Webmoney WMG (Эквивалент золота)")),
            array('value' => "MoneyMailR", 'label' => $helper->__("Электронная платежная система MoneyMail")),
            array('value' => "RuPayR", 'label' => $helper->__("Платежный сервис RBK Money")),
            array('value' => "W1R", 'label' => $helper->__("Платежный сервис Единый кошелек")),
            array('value' => "EasyPayB", 'label' => $helper->__("Электронные деньги EasyPay")),
            array('value' => "LiqPayZ", 'label' => $helper->__("Электронные платежи LiqPay")),
            array('value' => "MailRuR", 'label' => $helper->__("Электронные платежи Деньги@Mail.Ru")),
            array('value' => "ZPaymentR", 'label' => $helper->__("Платежная система Z-Payment")),
            array('value' => "TeleMoneyR", 'label' => $helper->__("Платежный сервис TeleMoney")),
            array('value' => "AlfaBankOceanR", 'label' => $helper->__("Интернет-Банк Альфа-Банк")),
            array('value' => "PSKBR", 'label' => $helper->__("Интернет-Банк Промсвязьбанк")),
            array('value' => "HandyBankMerchantOceanR", 'label' => $helper->__("Система интернет-банкинга HandyBank")),
            array('value' => "BSSFederalBankForInnovationAndDevelopmentR", 'label' => $helper->__("Федеральный банк инноваций и развития (АК ФБ Инноваций и Развития (ЗАО))")),
            array('value' => "BSSMezhtopenergobankR", 'label' => $helper->__("Интернет-банк Межтопэнергобанк")),
            array('value' => "RapidaOceanSvyaznoyR", 'label' => $helper->__("Салоны сети Связной")),
            array('value' => "RapidaOceanEurosetR", 'label' => $helper->__("Салоны сети Евросеть")),
            array('value' => "TerminalsElecsnetOceanR", 'label' => $helper->__("Терминалы самообслуживания Элекснет")),
            array('value' => "TerminalsUnikassaR", 'label' => $helper->__("Платежные терминалы Кассира.нет")),
            array('value' => "TerminalsMElementR", 'label' => $helper->__("Платежные терминалы Мобил Элемент")),
            array('value' => "TerminalsAbsolutplatR", 'label' => $helper->__("Платежные терминалы Абсолют Плат")),
            array('value' => "TerminalsPinpayR", 'label' => $helper->__("Платежные терминалы Pinpay")),
            array('value' => "TerminalsMoneyMoneyR", 'label' => $helper->__("Платежные терминалы Money-Money")),
            array('value' => "TerminalsPkbR", 'label' => $helper->__("Банкоматы банка Петрокоммерц")),
            array('value' => "VTB24R", 'label' => $helper->__("Банк ВТБ24")),
            array('value' => "MtsR", 'label' => $helper->__("Мобильный оператор МТС")),
            array('value' => "MegafonR", 'label' => $helper->__("Мобильный оператор Мегафон")),
            array('value' => "BANKOCEANCHECKR", 'label' => $helper->__("Оплата с помощью Iphone")),
            array('value' => "IFreeR", 'label' => $helper->__("Оплата с помощью SMS(i-FREE)")),
            array('value' => "ContactR", 'label' => $helper->__("Платежная система Contact"))
        );

        return $data;
    }
}