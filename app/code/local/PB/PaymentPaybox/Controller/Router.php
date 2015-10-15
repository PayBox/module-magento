<?php

class PB_PaymentPaybox_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    protected $_routerController;
    protected $_routerName = 'pbpaybox';
    protected $_moduleName = 'pbpaymentpaybox';


    public function initControllerRouters($observer)
    {
        $front = $observer->getEvent()->getFront();
        $this->setRouterController();
        $front->addRouter($this->_routerName, $this->_routerController);
    }

    public function setRouterController()
    {
        $this->_routerController = new PB_PaymentPaybox_Controller_Router();
    }

    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('install'))->sendResponse();
            exit;
        }

        $identifier = trim($request->getPathInfo(), '/');
        $fullPath = explode("/", $identifier);

        if ($this->_isOurModule($fullPath[0]))
            return false;

        $fullPath[2] = $fullPath[1];
        $fullPath[1] = 'gate';

        if ($orderId = $request->getParam("pg_order_id"))
            Mage::app()->getStore()->load(Mage::getModel("sales/order")->load($orderId)->getStoreId());

        $request->setModuleName($this->_moduleName)
                ->setControllerName(isset($fullPath[1]) ? $fullPath[1] : 'gate')
                ->setActionName(isset($fullPath[2]) ? $fullPath[2] : 'success');
        $request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, trim($request->getPathInfo(), '/'));

        return true;
    }

    /**
     * Проверка модуля по префиксу
     *
     * @param $urlPrefix
     * @return bool
     */
    protected function _isOurModule($urlPrefix)
    {
        return ($urlPrefix != $this->_routerName);
    }
}