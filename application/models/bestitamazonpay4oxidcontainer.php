<?php

$vendorPath = realpath(dirname(__FILE__).'/../../').'/vendor/autoload.php';

if (file_exists($vendorPath) === true) {
    include_once $vendorPath;
}

use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Container for needed initialized objects
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4OxidContainer implements LoggerAwareInterface
{
    /**
     * Log directory
     */
    const LOG_DIR = 'log/bestitamazon/';

    /**
     * @var null|oxUser
     */
    protected $_oActiveUserObject = null;

    /**
     * @var null|bestitAmazonPay4OxidAddressUtil
     */
    protected $_oAddressUtilObject = null;

    /**
     * @var null|bestitAmazonPay4OxidClient
     */
    protected $_oClientObject = null;

    /**
     * @var null|oxConfig
     */
    protected $_oConfigObject = null;

    /**
     * @var null|DatabaseInterface
     */
    protected $_oDatabaseObject = null;

    /**
     * @var null|bestitAmazonPay4OxidIpnHandler
     */
    protected $_oIpnHandlerObject = null;

    /**
     * @var null|oxLang
     */
    protected $_oLanguageObject = null;

    /**
     * @var null|bestitAmazonPay4OxidLoginClient
     */
    protected $_oLoginClientObject = null;

    /**
     * @var null|bestitAmazonPay4Oxid
     */
    protected $_oModuleObject = null;

    /**
     * @var null|bestitAmazonPay4OxidObjectFactory
     */
    protected $_oObjectFactory = null;

    /**
     * @var null|oxSession
     */
    protected $_oSessionObject = null;

    /**
     * @var null|oxUtilsDate
     */
    protected $_oUtilsDateObject = null;

    /**
     * @var null|oxUtilsServer
     */
    protected $_oUtilsServerObject = null;

    /**
     * @var null|oxUtils
     */
    protected $_oUtilsObject = null;

    /**
     * @var null|oxUtilsView
     */
    protected $_oUtilsViewObject = null;

    /**
     * @var null|bestitAmazonPay4OxidBasketUtil
     */
    protected $_oBasketUtil = null;

    /**
     * @var null|Logger
     */
    protected $_oLogger;

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->_oLogger = $logger;
    }

    /**
     * Returns the active user object.
     *
     * @return oxUser|bool
     * @throws oxSystemComponentException
     */
    public function getActiveUser()
    {
        if ($this->_oActiveUserObject === null) {
            $this->_oActiveUserObject = false;

            /** @var oxUser $oUser */
            $oUser = $this->getObjectFactory()->createOxidObject('oxUser');

            if ($oUser->loadActiveUser() === true) {
                $this->_oActiveUserObject = $oUser;
            }
        }

        return $this->_oActiveUserObject;
    }

    /**
     * @return bestitAmazonPay4OxidAddressUtil
     */
    public function getAddressUtil()
    {
        if ($this->_oAddressUtilObject === null) {
            $this->_oAddressUtilObject = oxRegistry::get('bestitAmazonPay4OxidAddressUtil');
        }

        return $this->_oAddressUtilObject;
    }

    /**
     * @return bestitAmazonPay4OxidClient
     */
    public function getClient()
    {
        if ($this->_oClientObject === null) {
            $this->_oClientObject = oxRegistry::get('bestitAmazonPay4OxidClient');
        }

        return $this->_oClientObject;
    }

    /**
     * Get the logger
     *
     * @param  string $name The name of the logger
     *
     * @return Logger
     */
    public function getLogger($name = 'AmazonPay')
    {
        // Cache the first logger init call, this allows us to use the same logger for the whole request context
        if ($this->_oLogger === null) {
            $sLogFile = $this->getConfig()->getConfigParam('sShopDir') . self::LOG_DIR;
            $logLevel = $this->getConfig()->getConfigParam('blAmazonLoggingLevel');
            $logActive = $this->getConfig()->getConfigParam('blAmazonLogging');
            $this->_oLogger = oxNew('bestitAmazonPay4OxidLogger', $sLogFile, $logLevel, $logActive, $name);
        }

        return $this->_oLogger;
    }

    /**
     * Returns the config object.
     *
     * @return oxConfig
     */
    public function getConfig()
    {
        if ($this->_oConfigObject === null) {
            $this->_oConfigObject = oxRegistry::getConfig();
        }

        return $this->_oConfigObject;
    }

    /**
     * Returns the database object.
     *
     * @return DatabaseInterface
     * @throws oxConnectionException
     */
    public function getDatabase()
    {
        if ($this->_oDatabaseObject === null) {
            $this->_oDatabaseObject = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        }

        return $this->_oDatabaseObject;
    }

    /**
     * Returns the ipn handler object.
     *
     * @return bestitAmazonPay4OxidIpnHandler
     */
    public function getIpnHandler()
    {
        if ($this->_oIpnHandlerObject === null) {
            $this->_oIpnHandlerObject = oxRegistry::get('bestitAmazonPay4OxidIpnHandler');
        }

        return $this->_oIpnHandlerObject;
    }

    /**
     * Returns the language object.
     *
     * @return oxLang
     */
    public function getLanguage()
    {
        if ($this->_oLanguageObject === null) {
            $this->_oLanguageObject = oxRegistry::getLang();
        }

        return $this->_oLanguageObject;
    }

    /**
     * @return bestitAmazonPay4OxidLoginClient
     */
    public function getLoginClient()
    {
        if ($this->_oLoginClientObject === null) {
            $this->_oLoginClientObject = oxRegistry::get('bestitAmazonPay4OxidLoginClient');
        }

        return $this->_oLoginClientObject;
    }

    /**
     * @return bestitAmazonPay4Oxid
     */
    public function getModule()
    {
        if ($this->_oModuleObject === null) {
            $this->_oModuleObject = oxRegistry::get('bestitAmazonPay4Oxid');
        }

        return $this->_oModuleObject;
    }

    /**
     * @return bestitAmazonPay4OxidObjectFactory
     */
    public function getObjectFactory()
    {
        if ($this->_oObjectFactory === null) {
            $this->_oObjectFactory = oxRegistry::get('bestitAmazonPay4OxidObjectFactory');
        }

        return $this->_oObjectFactory;
    }

    /**
     * Returns the session object.
     *
     * @return oxSession
     */
    public function getSession()
    {
        if ($this->_oSessionObject === null) {
            $this->_oSessionObject = oxRegistry::getSession();
        }

        return $this->_oSessionObject;
    }

    /**
     * @return oxUtilsDate
     */
    public function getUtilsDate()
    {
        if ($this->_oUtilsDateObject === null) {
            $this->_oUtilsDateObject = oxRegistry::get('oxUtilsDate');
        }

        return $this->_oUtilsDateObject;
    }

    /**
     * @return oxUtilsServer
     */
    public function getUtilsServer()
    {
        if ($this->_oUtilsServerObject === null) {
            $this->_oUtilsServerObject = oxRegistry::get('oxUtilsServer');
        }

        return $this->_oUtilsServerObject;
    }

    /**
     * @return oxUtils
     */
    public function getUtils()
    {
        if ($this->_oUtilsObject === null) {
            $this->_oUtilsObject = oxRegistry::getUtils();
        }

        return $this->_oUtilsObject;
    }

    /**
     * @return oxUtilsView
     */
    public function getUtilsView()
    {
        if ($this->_oUtilsViewObject === null) {
            $this->_oUtilsViewObject = oxRegistry::get('oxUtilsView');
        }

        return $this->_oUtilsViewObject;
    }

    /**
     * @return bestitAmazonPay4OxidBasketUtil
     */
    public function getBasketUtil()
    {
        if ($this->_oBasketUtil === null) {
            $this->_oBasketUtil = oxRegistry::get('bestitAmazonPay4OxidBasketUtil');
        }

        return $this->_oBasketUtil;
    }
}
