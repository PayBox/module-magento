<?php

$installstatus = version_compare(Mage::getVersion(), '1.4.2.0', '>');

if ($installstatus) {
    /* @var $installer Mage_Sales_Model_Entity_Setup */
    $installer = $this;

    $installer->startSetup();
    $statusTable = $installer->getTable('sales/order_status');
    $statusStateTable = $installer->getTable('sales/order_status_state');
    $statusLabelTable = $installer->getTable('sales/order_status_label');

    $paymentStatuses = array();
    $paymentStatuses[] = array(
        'status' => 'waiting_paybox',
        'label' => 'Waiting Paybox.kz payment');
    $paymentStatuses[] = array(
        'status' => 'error_paybox',
        'label' => 'Error data from Paybox.kz');
    $paymentStatuses[] = array(
        'status' => 'paid_paybox',
        'label' => 'Paid by Paybox.kz');

    foreach ($paymentStatuses as $paymentStatus) {
        try {
            $installer->getConnection()->insertArray($statusTable, array('status', 'label'), array($paymentStatus));
        } catch (Exception $e) {
            //none
        }
    }

    $paymentStatusToState = array();
    $paymentStatusToState[] = array(
        'status' => 'waiting_paybox',
        'state' => 'new',
        'is_default' => 0
    );

    $paymentStatusToState[] = array(
        'status' => 'error_paybox',
        'state' => 'new',
        'is_default' => 0
    );

    $paymentStatusToState[] = array(
        'status' => 'paid_paybox',
        'state' => 'processing',
        'is_default' => 0
    );

    foreach ($paymentStatusToState as $statusToState) {
        try {
            $installer->getConnection()->insertArray(
                $statusStateTable,
                array('status', 'state', 'is_default'),
                array($statusToState)
            );
        } catch (Exception $e) {
            //none
        }
    }

    $installer->endSetup();
} else {

}