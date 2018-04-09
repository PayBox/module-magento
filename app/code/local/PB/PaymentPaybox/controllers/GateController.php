<?php

class PB_PaymentPaybox_GateController extends Mage_Core_Controller_Front_Action
{
    const MODULENAME = "pbpaymentpaybox";
    const PAYMENTNAME = "pbpaybox";

    /**
     * Check is session expire
     */
    protected function _expireAjax()
    {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
            exit;
        }
    }

    /**
     * Redirect to PayBox payment page
     *
     * @throws Exception
     */
    public function redirectAction()
    {
        /** @var  $helper PB_PaymentPaybox_Helper_Data */
        $helper = Mage::helper(self::MODULENAME);
        $session = Mage::getSingleton('checkout/session');
        $state = Mage_Sales_Model_Order::STATE_NEW;

        $status = Mage::getStoreConfig('payment/pbpaybox/order_status');
        if (strlen($status) == 0)
            $status = Mage::getModel('sales/order')->getConfig()->getStateDefaultStatus($state);

        $lastOrderId = $session->getLastOrderId();
        if (empty($lastOrderId)) {
            $helper->log("Error. Redirect to paybox.money failed. No data in session about order.");
            $this->getResponse()->setHeader('Content-type', 'text/html; charset=UTF8');
            $this->getResponse()->setBody($this->getLayout()->createBlock(self::MODULENAME.'/error')->toHtml());
            return;
        }

        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($lastOrderId);
        $order->setState($state, $status, $this->__('Customer redirected to payment Gateway Paybox.kz'), false);
        $order->save();

        $payment = $order->getPayment()->getMethodInstance();
        if (!$payment)
            $payment = Mage::getSingleton(self::MODULENAME."/method_".self::PAYMENTNAME);

        $dataForSending = $payment->preparePaymentData($order);

        $helper->log(array_merge(array('Data transfer' => 'To Paybox.kz'), $dataForSending));
        $this->getResponse()
             ->setHeader('Content-type', 'text/html; charset=UTF8');
        $this->getResponse()
             ->setBody($this->getLayout()
                            ->createBlock(self::MODULENAME.'/redirect')
                            ->setGateUrl($payment->getGateUrl())
                            ->setPostData($dataForSending)
                            ->toHtml());
    }

    /**
     * Response from PayBox
     *
     * @throws Exception
     */
    public function statusAction()
    {
        /** @var  $helper PB_PaymentPaybox_Helper_Data */
        $helper = Mage::helper("pbpaymentpaybox");
        $state = Mage_Sales_Model_Order::STATE_PROCESSING;
        $errorStatus = 'error_paybox';
        $answer = $this->getRequest()->getParams();
        $payment = Mage::getSingleton(self::MODULENAME . "/method_" . self::PAYMENTNAME);

        if ($payment->getOrderId($answer)) {
            /** @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order')->loadByIncrementId($payment->getOrderId($answer));

            $orderPaymentMethod = $order->getPayment()->getMethodInstance();

            $checkedAnswer = $orderPaymentMethod->checkAnswerData($answer);

            if (is_array($checkedAnswer)) {
                $answer['Errors'] = $helper->arrayToRawData($checkedAnswer);
            }
            $result = array(
                'answer' => new Varien_Object($answer),
                'order' => $order,
            );
            Mage::dispatchEvent('paybox_success_answer', $result);
            $answer = $result['answer']->getData();
            $answer = array_merge(array('Data transfer' => 'From Paybox.kz'), $answer);

            $helper->log($answer);

            if ($checkedAnswer === true) {
                Mage::dispatchEvent('paybox_success_answer_without_error', $result);
                if ($order->getState() == Mage_Sales_Model_Order::STATE_NEW) {
                    if ($order->canInvoice()) {
                        //for partial invoice
                        if (!($qtys = $orderPaymentMethod->getInvoiceQtys($order))) {
                            $qtys = array();
                        }
                        $invoice = $order->prepareInvoice($qtys);
                        $invoice->register();
                        $invoice->capture();
                        $order->addRelatedObject($invoice);
                    }
                    if (!($paidStatus = $orderPaymentMethod->getPaidStatus())) {
                        $paidStatus = 'paid_paybox';
                    }
                    $result = $this->_sendEmailAfterPaymentSuccess($order);

                    $order->setState($state,
                        $paidStatus,
                        $this->__($helper->__('The amount has been authorized and captured by Paybox.kz.')),
                        $result);
                    $order->save();
                }
                header('Content-type: text/xml');
                echo $payment->prepareResponseXML('Success');
            } else {
                if ($order->getId()) {
                    $order->addStatusToHistory($errorStatus, implode(" / ", $checkedAnswer), false);
                    $order->save();
                }
                header('Content-type: text/xml');
                echo $payment->prepareResponseXML(implode(" / ", $checkedAnswer), 'error');
            }
        } else {
            $errorAnswer = 'Incorrect answer. No order number.';
            $helper->log($errorAnswer);
            if (!$answer)
                $answer = 'No answer data';

            $helper->log($answer);
            header('Content-type: text/xml');
            echo $payment->prepareResponseXML($errorAnswer, 'error');
        }
        exit();
    }

    /**
     * Failure status
     *
     * @throws Exception
     */
    public function failureAction()
    {
        /** @var  $helper PB_PaymentPaybox_Helper_Data */
        $helper = Mage::helper(self::MODULENAME);

        $payment = Mage::getSingleton(self::MODULENAME . "/method_" . self::PAYMENTNAME);
        $answer = $this->getRequest()->getParams();

        if ($payment->getOrderId($answer)) {
            /** @var $order Mage_Sales_Model_Order */
            //$order = Mage::getModel('sales/order')->load($payment->getOrderId($answer));
            $order = Mage::getModel('sales/order')->loadByIncrementId($payment->getOrderId($answer));

            $result = array(
                'answer' => new Varien_Object($answer),
                'order' => $order,
            );

            Mage::dispatchEvent('paybox_failure_answer', $result);
            $answer = $result['answer']->getData();
            $answer = array_merge(array('Data transfer' => 'From Paybox.kz'), $answer);
            $helper->log($answer);
            if (Mage::getStoreConfig('payment/' . self::PAYMENTNAME . '/cart_refill')) {
                $helper->refillCart($order);
            }

            $order->addStatusToHistory(
                $order->getStatus(),
                $helper->__('Payment failed'),
                false
            );
            $order->save();
            $order->cancel()->save();

            $this->_redirect('checkout/onepage/failure');
        } else {
            $errorAnswer = 'Incorrect answer. No order number.';
            $helper->log($errorAnswer);
            if (!$answer) {
                $answer = 'No answer data';
            }
            $helper->log($answer);
            print($errorAnswer);
            //$this->_redirect('/');
        }
    }

    /**
     * Success status
     */
    public function successAction()
    {
        /** @var $session Mage_Checkout_Model_Session */
        $session = Mage::getSingleton('checkout/session');
        if (!$session->getLastOrderId() || !$session->getLastQuoteId() || !$session->getLastSuccessQuoteId()) {
            $answer = $this->getRequest()->getParams();
            Mage::dispatchEvent('paybox_no_session_data_for_success', $answer);
        }
        $this->_redirect("checkout/onepage/success");
    }

    /**
     * Send email
     * @param $order Mage_Sales_Model_Order
     * @return bool
     */
    protected function _sendEmailAfterPaymentSuccess($order)
    {
        if (!$order->sendNewOrderEmail())
            return false;
        else
            $order->setEmailSent(true);

        return true;
    }
}
