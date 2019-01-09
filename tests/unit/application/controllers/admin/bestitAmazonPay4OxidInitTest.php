<?php

require_once dirname(__FILE__).'/../../../bestitAmazon4OxidUnitTestCase.php';

use org\bovigo\vfs\vfsStream;

/**
 * Unit test for class bestitAmazonPay4Oxid_init
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid_init
 */
class bestitAmazonPay4OxidInitTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $oRoot;

    /**
     * Setup virtual file system.
     */
    public function setUp()
    {
        $this->oRoot = vfsStream::setup();
    }

    /**
     * @param oxConfig            $oConfig
     * @param DatabaseInterface   $oDatabase
     * @param oxUtilsView         $oUtilsView
     * @param oxModule            $oModule
     * @param oxModuleCache       $oModuleCache
     * @param oxModuleInstaller   $oModuleInstaller
     * @param oxDbMetaDataHandler $oDbMetaDataHandler
     *
     * @return bestitAmazonPay4Oxid_init
     * @throws ReflectionException
     */
    private function _getObject(
        oxConfig $oConfig,
        DatabaseInterface $oDatabase,
        oxUtilsView $oUtilsView,
        oxModule $oModule,
        oxModuleCache $oModuleCache,
        oxModuleInstaller $oModuleInstaller,
        oxDbMetaDataHandler $oDbMetaDataHandler
    ) {
        $oBestitAmazonPay4OxidInit = new bestitAmazonPay4Oxid_init();
        self::setValue($oBestitAmazonPay4OxidInit, '_oConfig', $oConfig);
        self::setValue($oBestitAmazonPay4OxidInit, '_oDatabase', $oDatabase);
        self::setValue($oBestitAmazonPay4OxidInit, '_oUtilsView', $oUtilsView);
        self::setValue($oBestitAmazonPay4OxidInit, '_oModule', $oModule);
        self::setValue($oBestitAmazonPay4OxidInit, '_oModuleCache', $oModuleCache);
        self::setValue($oBestitAmazonPay4OxidInit, '_oModuleInstaller', $oModuleInstaller);
        self::setValue($oBestitAmazonPay4OxidInit, '_oDbMetaDataHandler', $oDbMetaDataHandler);

        return $oBestitAmazonPay4OxidInit;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidInit = new bestitAmazonPay4Oxid_init();
        self::assertInstanceOf('bestitAmazonPay4Oxid_init', $oBestitAmazonPay4OxidInit);
    }

    /**
     * @group unit
     * @covers ::_getConfig()
     * @throws ReflectionException
     */
    public function testGetConfig()
    {
        $oBestitAmazonPay4OxidInit = new bestitAmazonPay4Oxid_init();
        self::setValue($oBestitAmazonPay4OxidInit, '_oConfig', null);
        self::assertInstanceOf('oxConfig', self::callMethod($oBestitAmazonPay4OxidInit, '_getConfig'));
        self::assertAttributeNotEmpty('_oConfig', $oBestitAmazonPay4OxidInit);
    }

    /**
     * @group unit
     * @covers ::_getDatabase()
     * @throws ReflectionException
     */
    public function testGetDatabase()
    {
        $oBestitAmazonPay4OxidInit = new bestitAmazonPay4Oxid_init();
        self::setValue($oBestitAmazonPay4OxidInit, '_oDatabase', null);
        self::assertInstanceOf('DatabaseInterface', self::callMethod($oBestitAmazonPay4OxidInit, '_getDatabase'));
        self::assertAttributeNotEmpty('_oDatabase', $oBestitAmazonPay4OxidInit);
    }

    /**
     * @group unit
     * @covers ::_getUtilsView()
     * @throws ReflectionException
     */
    public function testGetUtilsView()
    {
        $oBestitAmazonPay4OxidInit = new bestitAmazonPay4Oxid_init();
        self::assertInstanceOf('oxUtilsView', self::callMethod($oBestitAmazonPay4OxidInit, '_getUtilsView'));
        self::assertAttributeNotEmpty('_oUtilsView', $oBestitAmazonPay4OxidInit);
    }

    /**
     * @group unit
     * @covers ::_getModule()
     * @throws ReflectionException
     */
    public function testGetModule()
    {
        $oBestitAmazonPay4OxidInit = new bestitAmazonPay4Oxid_init();
        self::assertInstanceOf('oxModule', self::callMethod($oBestitAmazonPay4OxidInit, '_getModule'));
        self::assertAttributeNotEmpty('_oModule', $oBestitAmazonPay4OxidInit);
    }

    /**
     * @group unit
     * @covers ::_getModuleCache()
     * @throws ReflectionException
     */
    public function testGetModuleCache()
    {
        $oBestitAmazonPay4OxidInit = new bestitAmazonPay4Oxid_init();
        self::assertInstanceOf(
            'oxModuleCache',
            self::callMethod($oBestitAmazonPay4OxidInit, '_getModuleCache', array($this->_getOxidModuleMock()))
        );
        self::assertAttributeNotEmpty('_oModuleCache', $oBestitAmazonPay4OxidInit);
    }

    /**
     * @group unit
     * @covers ::_getModuleInstaller()
     * @throws ReflectionException
     */
    public function testGetModuleInstaller()
    {
        $oBestitAmazonPay4OxidInit = new bestitAmazonPay4Oxid_init();
        self::assertInstanceOf(
            'oxModuleInstaller',
            self::callMethod($oBestitAmazonPay4OxidInit, '_getModuleInstaller', array($this->_getOxidModuleCacheMock()))
        );
        self::assertAttributeNotEmpty('_oModuleInstaller', $oBestitAmazonPay4OxidInit);
    }

    /**
     * @group unit
     * @covers ::_getDbMetaDataHandler()
     * @throws ReflectionException
     */
    public function testGetDbMetaDataHandler()
    {
        $oBestitAmazonPay4OxidInit = new bestitAmazonPay4Oxid_init();
        self::assertInstanceOf('oxDbMetaDataHandler', self::callMethod($oBestitAmazonPay4OxidInit, '_getDbMetaDataHandler'));
        self::assertAttributeNotEmpty('_oDbMetaDataHandler', $oBestitAmazonPay4OxidInit);
    }

    /**
     * @group unit
     * @covers ::onActivate()
     * @covers ::clearTmp()
     * @covers ::_streamSafeGlob()
     * @covers ::_executeSqlFile()
     * @covers ::_removeTempVersionNumberFromDatabase()
     * @covers ::_deactivateOldModule()
     * @throws oxConnectionException
     * @throws ReflectionException
     */
    public function testOnActivate()
    {
        vfsStream::create(array(
            'smarty' => array(
                'testFile.php' => 'someContent'
            ),
            'testFile.txt' => 'someContent'
        ));

        $oConfig = $this->_getConfigMock();

        $oConfig->expects($this->exactly(3))
            ->method('getShopConfVar')
            ->with('sUpdateFrom', null, bestitAmazonPay4Oxid_init::TMP_DB_ID)
            ->will($this->onConsecutiveCalls(
                null,
                '2.2.1',
                '2.3.0'
            ));

        $oConfig->expects($this->exactly(8))
            ->method('getConfigParam')
            ->withConsecutive(
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sCompileDir'),
                array('sAmazonMode'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sCompileDir'),
                array('sAmazonMode'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sCompileDir')
            )
            ->will($this->onConsecutiveCalls(
                false,
                $this->oRoot->url(),
                'Sync',
                false,
                $this->oRoot->url(),
                'Async',
                true,
                $this->oRoot->url()
            ));

        $oConfig->expects($this->exactly(6))
            ->method('getShopId')
            ->will($this->returnValue(123));

        $oDatabase = $this->_getDatabaseMock();

        $oDatabase->expects($this->exactly(4))
            ->method('quote')
            ->withConsecutive(
                array(bestitAmazonPay4Oxid_init::TMP_DB_ID),
                array(123),
                array(bestitAmazonPay4Oxid_init::TMP_DB_ID),
                array(123)
            )
            ->will($this->returnCallback(function ($sValue) {
                return "'{$sValue}'";
            }));

        $sPaymentsQuery = "SELECT COUNT(OXID)
            FROM oxpayments
            WHERE OXID IN ('jagamazon', 'bestitamazon')";

        $sSignatureQuery = "SELECT OXVARVALUE
            FROM oxconfig
            WHERE OXVARNAME = 'sAmazonSignature'
              AND OXSHOPID = '123'";

        $oDatabase->expects($this->exactly(6))
            ->method('getOne')
            ->withConsecutive(
                array(new MatchIgnoreWhitespace($sPaymentsQuery)),
                array(new MatchIgnoreWhitespace($sSignatureQuery)),
                array(new MatchIgnoreWhitespace($sPaymentsQuery)),
                array(new MatchIgnoreWhitespace($sSignatureQuery)),
                array(new MatchIgnoreWhitespace($sPaymentsQuery)),
                array(new MatchIgnoreWhitespace($sSignatureQuery))
            )->will($this->onConsecutiveCalls(
                false,
                false,
                1,
                '',
                0,
                'signature'
            ));

        $sDbDir = dirname(__FILE__).'/../../../../../_db/';

        $sInstallSqlRows = file_get_contents($sDbDir.'install.sql');
        $aInstallSqlRows = explode(';', $sInstallSqlRows);
        $aInstallSqlRows = array_map('trim', $aInstallSqlRows);
        $sFirstSqlFile = file_get_contents($sDbDir.'update_2.2.2.sql');
        $aFirstSqlRows = explode(';', $sFirstSqlFile);
        $aFirstSqlRows = array_map('trim', $aFirstSqlRows);
        $sSecondSqlFile = file_get_contents($sDbDir.'update_2.4.0.sql');
        $aSecondSqlRows = explode(';', $sSecondSqlFile);
        $aSecondSqlRows = array_map('trim', $aSecondSqlRows);

        $oDatabase->expects($this->exactly(52))
            ->method('execute')
            ->withConsecutive(
                array(new MatchIgnoreWhitespace(
                    "UPDATE oxv_oxpayments_de SET OXACTIVE = 1 
                    WHERE OXID = 'bestitamazon'"
                )),
                array(new MatchIgnoreWhitespace(
                    "UPDATE oxv_oxpayments_de SET OXACTIVE = 1 
                    WHERE OXID = 'bestitamazon'"
                )),
                array(new MatchIgnoreWhitespace($aFirstSqlRows[0])),
                array(new MatchIgnoreWhitespace($aFirstSqlRows[1])),
                array(new MatchIgnoreWhitespace($aFirstSqlRows[2])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[0])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[1])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[2])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[3])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[4])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[5])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[6])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[7])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[8])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[9])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[10])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[11])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[12])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[13])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[14])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[15])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[16])),
                array(new MatchIgnoreWhitespace(
                    "DELETE
                    FROM `oxconfig`
                    WHERE oxmodule = 'module:bestitAmazonPay4Oxid_tmp'
                      AND oxshopid = '123'"
                )),
                array(new MatchIgnoreWhitespace($aInstallSqlRows[0])),
                array(new MatchIgnoreWhitespace($aInstallSqlRows[1])),
                array(new MatchIgnoreWhitespace($aInstallSqlRows[2])),
                array(new MatchIgnoreWhitespace($aInstallSqlRows[3])),
                array(new MatchIgnoreWhitespace($aInstallSqlRows[4])),
                array(new MatchIgnoreWhitespace($aInstallSqlRows[5])),
                array(new MatchIgnoreWhitespace($aInstallSqlRows[6])),
                array(new MatchIgnoreWhitespace($aInstallSqlRows[7])),
                array(new MatchIgnoreWhitespace($aInstallSqlRows[8])),
                array(new MatchIgnoreWhitespace(
                    "UPDATE oxv_oxpayments_de SET OXACTIVE = 1 
                    WHERE OXID = 'bestitamazon'"
                )),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[0])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[1])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[2])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[3])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[4])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[5])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[6])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[7])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[8])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[9])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[10])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[11])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[12])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[13])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[14])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[15])),
                array(new MatchIgnoreWhitespace($aSecondSqlRows[16])),
                array(new MatchIgnoreWhitespace(
                    "DELETE 
                    FROM `oxconfig` 
                    WHERE oxmodule = 'module:bestitAmazonPay4Oxid_tmp'
                      AND oxshopid = '123'"
                )),
                array(new MatchIgnoreWhitespace(
                    "UPDATE oxconfig 
                    SET OXVARTYPE = 'str' 
                    WHERE OXVARNAME = 'sAmazonSignature'
                      AND OXSHOPID = '123'"
                ))
            );

        $oUtilsView = $this->_getUtilsViewMock();

        $oUtilsView->expects($this->exactly(2))
            ->method('addErrorToDisplay')
            ->withConsecutive(
                array(new oxException('EXCEPTION_MODULE_NOT_LOADED')),
                array(new oxException('exceptionError'))
            );

        $oModule = $this->_getOxidModuleMock();

        $oModule->expects($this->exactly(2))
            ->method('load')
            ->with('jagamazonpayment4oxid')
            ->will($this->onConsecutiveCalls(false, true));

        $oModuleInstaller = $this->_getOxidModuleInstallerMock();

        $oModuleInstaller->expects($this->once())
            ->method('deactivate')
            ->will($this->returnCallback(function () {
                throw new oxException('exceptionError');
            }));

        $oDbMetaDataHandler = $this->_getOxidDbMetaDataHandler();

        $oDbMetaDataHandler->expects($this->once())
            ->method('updateViews');

        // check if config var blBestitAmazonPay4OxidEnableMultiCurrency get saved
        $oConfig->expects($this->once())
            ->method('saveShopConfVar')
            ->with('bool', 'blBestitAmazonPay4OxidEnableMultiCurrency', true, 123, 'module:bestitamazonpay4oxid');

        $oBestitAmazonPay4OxidInit = $this->_getObject(
            $oConfig,
            $oDatabase,
            $oUtilsView,
            $oModule,
            $this->_getOxidModuleCacheMock(),
            $oModuleInstaller,
            $oDbMetaDataHandler
        );

        $oBestitAmazonPay4OxidInit::onActivate();
        $oBestitAmazonPay4OxidInit::onActivate();
        $oBestitAmazonPay4OxidInit::onActivate();

        if (method_exists($this, 'assertLoggedException')
            && class_exists('OxidEsales\Eshop\Core\Exception\StandardException')
            && method_exists('OxidEsales\Eshop\Core\Exception\StandardException', 'debugOut')
        ) {
            $this->assertLoggedException(
                'OxidEsales\Eshop\Core\Exception\StandardException',
                'exceptionError'
            );
        }
    }

    /**
     * @group unit
     * @covers ::onDeactivate()
     * @throws oxConnectionException
     * @throws ReflectionException
     */
    public function testOnDeactivate()
    {
        $oDatabase = $this->_getDatabaseMock();
        $oDatabase->expects($this->once())
            ->method('execute')
            ->with(new MatchIgnoreWhitespace(
                "UPDATE oxv_oxpayments_de SET OXACTIVE = 0
                WHERE OXID = 'bestitamazon'"
            ));

        $oBestitAmazonPay4OxidInit = $this->_getObject(
            $this->_getConfigMock(),
            $oDatabase,
            $this->_getUtilsViewMock(),
            $this->_getOxidModuleMock(),
            $this->_getOxidModuleCacheMock(),
            $this->_getOxidModuleInstallerMock(),
            $this->_getOxidDbMetaDataHandler()
        );

        $oBestitAmazonPay4OxidInit::onDeactivate();
    }

    /**
     * @group unit
     * @covers ::getCurrentVersion()
     * @throws ReflectionException
     */
    public function testGetCurrentVersion()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(5))
            ->method('getConfigParam')
            ->with('aModuleVersions')
            ->will($this->onConsecutiveCalls(
                null,
                array('some' => 0, 'jagAmazonPayment4Oxid' => 12),
                array('some' => 0, 'jagamazonpayment4oxid' => 23),
                array('some' => 0, 'bestitAmazonPay4Oxid' => 34),
                array('some' => 0, 'bestitamazonpay4oxid' => 45)
            ));

        $oBestitAmazonPay4OxidInit = $this->_getObject(
            $oConfig,
            $this->_getDatabaseMock(),
            $this->_getUtilsViewMock(),
            $this->_getOxidModuleMock(),
            $this->_getOxidModuleCacheMock(),
            $this->_getOxidModuleInstallerMock(),
            $this->_getOxidDbMetaDataHandler()
        );

        self::assertNull($oBestitAmazonPay4OxidInit::getCurrentVersion());
        self::assertEquals(12, $oBestitAmazonPay4OxidInit::getCurrentVersion());
        self::assertEquals(23, $oBestitAmazonPay4OxidInit::getCurrentVersion());
        self::assertEquals(34, $oBestitAmazonPay4OxidInit::getCurrentVersion());
        self::assertEquals(45, $oBestitAmazonPay4OxidInit::getCurrentVersion());
    }

    /**
     * @group unit
     * @covers ::flagForUpdate()
     * @throws ReflectionException
     */
    public function testFlagForUpdate()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->once())
            ->method('saveShopConfVar')
            ->with('str', 'sUpdateFrom', 12, null, bestitAmazonPay4Oxid_init::TMP_DB_ID);

        $oConfig->expects($this->once())
            ->method('getConfigParam')
            ->with('aModuleVersions')
            ->will($this->returnValue(array('some' => 0, 'jagAmazonPayment4Oxid' => 12)));

        $oBestitAmazonPay4OxidInit = $this->_getObject(
            $oConfig,
            $this->_getDatabaseMock(),
            $this->_getUtilsViewMock(),
            $this->_getOxidModuleMock(),
            $this->_getOxidModuleCacheMock(),
            $this->_getOxidModuleInstallerMock(),
            $this->_getOxidDbMetaDataHandler()
        );

        $oBestitAmazonPay4OxidInit::flagForUpdate();
    }
}
