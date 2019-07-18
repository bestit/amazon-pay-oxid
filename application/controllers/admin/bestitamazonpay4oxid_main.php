<?php

/**
 * Controller for Amazon Pay tab on orders view
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4Oxid_main extends oxAdminDetails
{
    /**
     * @var null|bestitAmazonPay4OxidContainer
     */
    protected $_oContainer = null;

    /**
     * Returns the active user object.
     *
     * @return bestitAmazonPay4OxidContainer
     * @throws oxSystemComponentException
     */
    protected function _getContainer()
    {
        if ($this->_oContainer === null) {
            $this->_oContainer = oxNew('bestitAmazonPay4OxidContainer');
        }

        return $this->_oContainer;
    }

    /**
     * Returns user payment used for current order. In case current order was executed using
     * credit card and user payment info is not stored in db (if $this->_getContainer()->getConfig()->blStoreCreditCardInfo = false),
     * just for preview user payment is set from oxPayment
     *
     * @param oxOrder $oOrder Order object
     *
     * @return oxUserPayment
     * @throws oxSystemComponentException
     */
    protected function _getPaymentType(oxOrder $oOrder)
    {
        $oUserPayment = $oOrder->getPaymentType();
        $sPaymentType = $oOrder->getFieldData('oxpaymenttype');

        if ($oUserPayment === false && $sPaymentType) {
            $oPayment = $this->_getContainer()->getObjectFactory()->createOxidObject('oxPayment');

            if ($oPayment->load($sPaymentType)) {
                // in case due to security reasons payment info was not kept in db
                $oUserPayment = $this->_getContainer()->getObjectFactory()->createOxidObject('oxUserPayment');
                $oUserPayment->assign(array('oxdesc' => $oPayment->getFieldData('oxdesc')));
            }
        }

        return $oUserPayment;
    }

    /**
     * Executes parent method parent::render(), creates oxOrder, passes
     * it's data to Smarty engine and returns name of template file
     * "order_overview.tpl".
     *
     * @return string
     * @throws oxSystemComponentException
     */
    public function render()
    {
        parent::render();

        /** @var oxOrder $oOrder */
        $oOrder = $this->_getContainer()->getObjectFactory()->createOxidObject('oxOrder');
        $oConfig = $this->_getContainer()->getConfig();
        $oCurrency = $oConfig->getActShopCurrencyObject();
        $oLang = $this->_getContainer()->getLanguage();

        $sId = $this->getEditObjectId();

        if ((int)$sId !== -1 && $sId !== null && $oOrder->load($sId)) {
            $this->_aViewData['edit'] = $oOrder;
            $this->_aViewData['aProductVats'] = $oOrder->getProductVats();
            $this->_aViewData['orderArticles'] = $oOrder->getOrderArticles();
            $this->_aViewData['giftCard'] = $oOrder->getGiftCard();
            $this->_aViewData['paymentType'] = $this->_getPaymentType($oOrder);
            $this->_aViewData['deliveryType'] = $oOrder->getDelSet();
            $sTsProtectedCosts = $oOrder->getFieldData('oxtsprotectcosts');

            if ($sTsProtectedCosts) {
                $this->_aViewData['tsprotectcosts'] = $oLang->formatCurrency(
                    $sTsProtectedCosts,
                    $oCurrency
                );
            }
        }

        // orders today
        $dSum = $oOrder->getOrderSum(true);
        $this->_aViewData['ordersum'] = $oLang->formatCurrency($dSum, $oCurrency);
        $this->_aViewData['ordercnt'] = $oOrder->getOrderCnt(true);

        // ALL orders
        $dSum = $oOrder->getOrderSum();
        $this->_aViewData['ordertotalsum'] = $oLang->formatCurrency($dSum, $oCurrency);
        $this->_aViewData['ordertotalcnt'] = $oOrder->getOrderCnt();
        $this->_aViewData['afolder'] = $oConfig->getConfigParam('aOrderfolder');
        $this->_aViewData['currency'] = $oCurrency;

        return 'bestitamazonpay4oxid_main.tpl';
    }

    /**
     * Sends refund request to Amazon
     * @throws Exception
     */
    public function refundAmazonOrder()
    {
        $oConfig = $this->_getContainer()->getConfig();
        $sId = $this->getEditObjectId();

        if ((int)$sId !== -1
            && $sId !== null
            && (int)$oConfig->getRequestParameter('blAmazonConfirmRefund') === 1
        ) {
            /** @var oxOrder $oOrder */
            $oOrder = $this->_getContainer()->getObjectFactory()->createOxidObject('oxOrder');
            $oOrder->load($sId);
            $fAmazonRefundAmount = (float)str_replace(
                ',',
                '.',
                $oConfig->getRequestParameter('fAmazonRefundAmount')
            );

            if ($fAmazonRefundAmount > 0) {
                $this->_getContainer()->getClient()->refund($fAmazonRefundAmount, $oOrder);
            } else {
                $this->_aViewData['bestitrefunderror'] = $this->_getContainer()->getLanguage()
                    ->translateString('BESTITAMAZONPAY_INVALID_REFUND_AMOUNT');
            }
        } else {
            $this->_aViewData['bestitrefunderror'] = $this->_getContainer()->getLanguage()
                ->translateString('BESTITAMAZONPAY_PLEASE_CHECK_REFUND_CHECKBOX');
        }
    }


    /**
     * Gets All refunds for the order
     *
     * @return array
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    public function getRefunds()
    {
        $oDb = $this->_getContainer()->getDatabase();

        $sSql = "SELECT *
            FROM bestitamazonrefunds
            WHERE OXORDERID = {$oDb->quote($this->getEditObjectId())}
            ORDER BY TIMESTAMP";

        return $oDb->getAll($sSql);
    }

    /**
     * Gets refunds status for the order
     * @throws Exception
     */
    public function getRefundsStatus()
    {
        $sId = $this->getEditObjectId();

        if ((int)$sId !== -1 && $sId !== null) {
            $oDb = $this->_getContainer()->getDatabase();

            $sSql = "SELECT BESTITAMAZONREFUNDID
                FROM bestitamazonrefunds
                WHERE STATE = 'Pending'
                  AND BESTITAMAZONREFUNDID != ''
                  AND OXORDERID = {$oDb->quote($sId)}";

            $aResult = $oDb->getAll($sSql);

            foreach ($aResult as $aRow) {
                $this->_getContainer()->getClient()->getRefundDetails($aRow['BESTITAMAZONREFUNDID']);
            }
        }
    }
}
