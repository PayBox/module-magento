<?php

class PB_PaymentPaybox_Model_Method_Pbpaybox extends Mage_Payment_Model_Method_Abstract
{

    protected $_code = 'pbpaybox';
    protected $_formBlockType = 'pbpaymentpaybox/single_form';
    protected $_infoBlockType = 'pbpaymentpaybox/single_info';

    /**
     * Payment Method features
     * @var bool
     */
    protected $_isGateway = true;
    protected $_canOrder = false;
    protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = false;
    protected $_canUseRobonal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_isInitializeNeeded = false;
    protected $_canFetchTransactionInfo = false;
    protected $_canReviewPayment = false;
    protected $_canCreateBillingAgreement = false;
    protected $_canManageRecurringProfiles = false;
    protected $_canEdit = false;

    protected $_canUseInternal = false; // Payment method will not work in admin panel order

    /**
     * Payment Method instance configuration
     * @var bool
     */
    protected $_isActive = 0;
    protected $_title;
    protected $_description;
    protected $_testMode = 0;

    protected $_isLogenabled = 0;


    protected $_sMerchantID;
    protected $_sInvDesc;
    protected $_paymentText;
    protected $_sIncCurrLabel;
    protected $_sLang;
    protected $_transferCurrency;


    protected $_sSecretKey;
    protected $_sMerchantPassTwo;

    protected $_cartRefill;

    protected $_gateUrl = "https://paybox.kz/payment.php";


    protected $_configRead = false;


    public function __construct()
    {
        parent::__construct();
        $this->readConfig();
    }


    /**
     * Read configuration from system.xml
     *
     * @return void
     */
    protected function readConfig()
    {
        if ($this->_configRead)
            return;

        $this->_isActive = $this->getConfigData('active');
        $this->_title = $this->getConfigData('title');
        $this->_testMode = $this->getConfigDataPB('test_mode');

        $this->_paymentText = $this->getConfigData('payment_text');
        $this->_description = Mage::helper('cms')->getBlockTemplateProcessor()->filter($this->_paymentText);

        $this->_sMerchantID = $this->getConfigDataPB('sMerchantID');
        $this->_sSecretKey = Mage::helper('core')->decrypt($this->getConfigDataPB('sSecretKey'));

        $this->_sInvDesc = $this->getConfigDataPB('sInvDesc');
        $this->_transferCurrency = Mage::app()->getBaseCurrencyCode();

        $this->_sLang = $this->getConfigDataPB('sLang');
        $this->_cartRefill = $this->getConfigDataPB('cart_refill');

        $this->_configRead = true;

        return;
    }

    /**
     * To read common settings, like login and passwords.
     *
     * @param $field
     * @param $storeId
     * @return mixed
     */
    public function getConfigDataPB($field, $storeId = null)
    {
        if (null === $storeId)
            $storeId = $this->getStore();

        $path = 'payment/pbpaybox/' . $field;
        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Get description
     *
     * @return mixed
     */
    public function getDescription()
    {
        $this->readConfig();
        return $this->_description;
    }

    /**
     * Get redirect URL
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->getRedirectUrl();
    }

    /**
     * Get redirect URL from config
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return Mage::getUrl('pbpaybox/redirect');
    }

    /**
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        return parent::isAvailable($quote);
    }

    /**
     * Check currency
     *
     * @param string $currencyCode
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function canUseForCurrency($currencyCode)
    {
        $baseCurrency = Mage::app()->getWebsite()->getBaseCurrency();
        $rate = $baseCurrency->getRate($this->_transferCurrency);
        $displayCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $reverseRate = $baseCurrency->getRate($displayCurrencyCode);
        if (!$rate || !$reverseRate) {
            Mage::helper("pbpaymentpaybox")->log('There is no rate for [' . $displayCurrencyCode . "/"
                . $this->_transferCurrency
                . '] to convert order amount. Payment method Paybox.kz not displayed.');
            return false;
        }
        return true;
    }

    /**
     * Check country
     *
     * @param $country
     * @return bool
     */
    public function canUseForCountry($country)
    {
        if (Mage::getStoreConfig('payment/pbpaybox/allowspecific') == 1) {
            $availableCountries = explode(',', Mage::getStoreConfig('payment/pbpaybox/specificcountry'));
            if (!in_array($country, $availableCountries)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Prepare payment data for sending to PayBox
     *
     * @param $order Mage_Sales_Model_Order
     * @return array
     */
    public function preparePaymentData($order)
    {
        $this->readConfig();

        $customer_id = $order->getCustomerId();
        $customerData = Mage::getModel('customer/customer')->load($customer_id);

        $phone = $customerData->getPrimaryBillingAddress()->getTelephone();
        $phone = str_replace(array('+', '-', ' ', '(', ')'), array('', '', '', '', ''), $phone);
        $phone[0] = $phone[0] == 8 ?: 7;


        if (empty($this->_sMerchantID) || empty($this->_sSecretKey)) {
            Mage::helper("pbpaymentpaybox")->log('Please enter your Paybox merchant_id and secret_key in admin panel!');
        }

        $outSum = $this->getOutSum($order);

        $signatureHelper = Mage::helper("pbpaymentpaybox/signature");
        $baseUrl = Mage::getBaseUrl('web');

        $postData = array(
            "pg_merchant_id" => $this->_sMerchantID,
            "pg_order_id" => $order->getIncrementId(),
            "pg_amount" => round($outSum, 2),
            "pg_currency" => "KZT",
            "pg_description" => $this->_sInvDesc,
            "pg_user_contact_email" => $order->getCustomerEmail(),
            "pg_user_phone" => $phone,
            "pg_language" => $this->_sLang,
            "pg_salt" => $signatureHelper->makeSalt(),
            "pg_result_url" => $baseUrl . 'pbpaybox/status',
            "pg_request_method" => 'POST',
            "pg_success_url" => $baseUrl . 'pbpaybox/success',
            "pg_success_url_method" => 'AUTOPOST',
            "pg_failure_url" => $baseUrl . 'pbpaybox/failure',
            "pg_failure_url_method" => 'AUTOPOST',
            "pg_testing_mode" => $this->_testMode,
        );

        $postData['pg_sig'] = $signatureHelper->make('payment.php', $postData, $this->_sSecretKey);

        $result = array(
            'postData' => new Varien_Object($postData),
            'order' => $order,
        );
        Mage::dispatchEvent('paybox_prepare_payment_data', $result);
        $postData = $result['postData']->getData();
        return $postData;
    }

    /**
     * Check answer from PayBox gate
     *
     * @param $answer
     * @return array|bool
     */
    public function checkAnswerData($answer)
    {
        try {
            $errors = array();
            $this->readConfig();

            $order = Mage::getModel("sales/order")->loadByIncrementId($this->getOrderId($answer));

            $signatureHelper = Mage::helper("pbpaymentpaybox/signature");

            $hashArray = $answer;
            if(array_key_exists('/pbpaybox/status', $hashArray))
                unset($hashArray['/pbpaybox/status']);

            $correctHash = $signatureHelper->check($hashArray['pg_sig'], 'status', $hashArray, $this->_sSecretKey);

            if (!$correctHash) {
                $errors[] = "Incorrect HASH - fraud data or wrong secret Key";
                $errors[] = "Maybe success payment";
            }

            /**
             * @var $order Mage_Sales_Model_Order
             */
            if ($this->_transferCurrency != $order->getOrderCurrencyCode())
                $outSum = round($order->getBaseCurrency()->convert($order->getBaseGrandTotal(), $this->_transferCurrency), 2);
            else
                $outSum = round($order->getGrandTotal(), 2);

            if ($outSum != $answer["pg_amount"])
                $errors[] = "Incorrect Amount: " . $answer["pg_amount"] . " (need: " . $outSum . ")";

            if (count($errors) > 0)
                return $errors;

            return (bool)$correctHash;
        } catch (Exception $e) {
            return array("Internal error:" . $e->getMessage());
        }
    }

    /**
     * Prepare answer for PayBox status request
     *
     * @param $description
     * @param string $status
     * @return string
     */
    public function prepareResponseXML($description, $status='ok')
    {
        $signatureHelper = Mage::helper("pbpaymentpaybox/signature");

        $postData = array(
            "pg_salt" => $signatureHelper->makeSalt(),
            "pg_status" => $status,
            "pg_description" => $description
        );

        $postData['pg_sig'] = $signatureHelper->make('status', $postData, $this->_sSecretKey);

        return "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<response>
    <pg_salt>".$postData['pg_salt']."</pg_salt>
    <pg_status>".$postData['pg_status']."</pg_status>
    <pg_description>".$postData['pg_description']."</pg_description>
    <pg_sig>".$postData['pg_sig']."</pg_sig>
</response>";

    }

    /**
     * Get PayBox gate URL
     *
     * @return string
     */
    public function getGateUrl()
    {
        return $this->_gateUrl;
    }

    /**
     * Get OrderID from answer
     *
     * @param $answer
     * @return string
     */
    public function getOrderId($answer)
    {
        return isset($answer["pg_order_id"]) ? $answer["pg_order_id"] : "";
    }

    /**
     * Calculate amount for PayBox
     *
     * @param $order Mage_Sales_Model_Order
     * @param bool $writeLog
     *
     * @return float
     */
    public function getOutSum($order, $writeLog = false)
    {
        if ($this->_transferCurrency != $order->getOrderCurrencyCode()) {
            $outSum = $order->getBaseCurrency()->convert($order->getBaseGrandTotal(), $this->_transferCurrency);
            if ($writeLog) {
                Mage::helper("pbpaymentpaybox")->log('Currency converted from [' .
                    Mage::app()->getStore()->getCurrentCurrencyCode() . '] to [' .
                    $this->_transferCurrency . '] ' . $order->getBaseGrandTotal() . ' ' . $outSum . '');
            }
        } else {
            $outSum = $order->getGrandTotal();
        }

        return $outSum;
    }

}