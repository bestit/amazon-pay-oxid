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
    protected $logLevel;

    /**
     * Is the log active
     *
     * @var bool
     */
    protected $logActive;

    /**
     * bestitamazonpay4oxidlogger constructor.
     *
     * @param string $sLogFile  The logfile
     * @param string $logLevel
     * @param bool   $logActive
     * @param string $name
     */
    public function __construct($sLogFile, $logLevel, $logActive, $name = 'AmazonPay')
    {
        $sLogFile .= strtolower($name . '.log');
        $handler = new RotatingFileHandler($sLogFile, 14);

        $handler->pushProcessor(new UidProcessor());
        $handler->pushProcessor(new MemoryUsageProcessor());
        $handler->setFormatter($formatter = new LineFormatter());
        $formatter->includeStacktraces();

        parent::__construct($name, array($handler));

        $this->logLevel = Logger::ERROR;
        if (strtolower($logLevel) === 'debug') {
            $this->logLevel = Logger::DEBUG;
        }
        $this->logActive = $logActive;
    }

    /**
     * Adds a log record.
     *
     * @param  int    $level   The logging level
     * @param  string $message The log message
     * @param  array  $context The log context
     * @return bool Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = array())
    {
        $result = false;
        if ($this->logActive && $level >= $this->logLevel) {
            $result = parent::addRecord($level, $message, $context);
        }

        return $result;
    }
}
