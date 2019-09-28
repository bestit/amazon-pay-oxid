<?php

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;

/**
 * PSR logger for the oxap module
 *
 * @author Martin Knoop <info@bestit-online.de>
 */
class bestitAmazonPay4OxidLogger extends Logger
{
    /**
     * The loglevel that is configured
     *
     * @var int
     */
    protected $iLogLevel;

    /**
     * Is the log active
     *
     * @var bool
     */
    protected $blLogActive;

    /**
     * bestitamazonpay4oxidlogger constructor.
     *
     * @param string $sLogFile    The logfile
     * @param string $sLogLevel
     * @param bool   $blLogActive
     * @param string $sName
     */
    public function __construct($sLogFile, $sLogLevel, $blLogActive, $sName = 'AmazonPay')
    {
        $sLogFile .= strtolower($sName . '.log');
        $oHandler = new RotatingFileHandler($sLogFile, 14);

        $oHandler->pushProcessor(new UidProcessor());
        $oHandler->pushProcessor(new MemoryUsageProcessor());
        $oFormatter = new LineFormatter();
        $oHandler->setFormatter($oFormatter);
        $oFormatter->includeStacktraces();

        parent::__construct($sName, array($oHandler));

        $this->iLogLevel = Logger::ERROR;
        if (strtolower($sLogLevel) === 'debug') {
            $this->iLogLevel = Logger::DEBUG;
        }
        $this->blLogActive = $blLogActive;
    }

    /**
     * Adds a log record.
     *
     * @param  int    $iLevel   The logging level
     * @param  string $sMessage The log message
     * @param  array  $aContext The log context
     * @return bool Whether the record has been processed
     */
    public function addRecord($iLevel, $sMessage, array $aContext = array())
    {
        $result = false;
        if ($this->blLogActive && $iLevel >= $this->iLogLevel) {
            $result = parent::addRecord($iLevel, $sMessage, $aContext);
        }

        return $result;
    }
}
