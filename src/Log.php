<?php

namespace App;

use BadMethodCallException;
use RuntimeException;

/**
 * Simple PHP logger class
 *
 * Call static methods
 *
 * @method static emergency($message)
 * @method static alert($message)
 * @method static critical($message)
 * @method static error($message)
 * @method static warning($message)
 * @method static notice($message)
 * @method static info($message)
 * @method static debug($message)
 */
class Log
{
    /**
     * RFC 5424 LEVELS
     */
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    private static $messages = [];

    /**
     * Logger options
     *
     * @var string[]
     */
    private static $options = [
        'logFileDir' => __DIR__ . '/../log', // dir contains your logs
        'logFileFormat' => 'log-%D%.log', // %DIR% - log file dir, %D% - date
        'logMessageFormat' => '[%D%]: %L% - %M%', // %D% - date, %L% - log level, %M% - message
        'logArrayInOneRow' => false,
        'overwriteLogFile' => false,
        'immediatelyWriteLog' => false,
        'dateFileFormat' => 'Y-m-d',
        'dateMessageFormat' => 'Y-m-d H:i:s'
    ];

    /**
     * @var float
     */
    private static $time;

    /**
     * @var bool
     */
    private static $firstLog = true;

    /**
     * Prepare log message for further logging
     *
     * @param $message - can be string or an array
     *
     * @return void
     */
    public static function log($message, $level = self::INFO)
    {
        if (is_array($message)) {
            $message = rtrim(print_r($message, true), PHP_EOL);
            if (self::$options['logArrayInOneRow']) {
                $message = str_replace(['    ', PHP_EOL], '', $message);
            }
        }

        $message = self::formatMessage($message, $level) . PHP_EOL;

        if (self::$options['immediatelyWriteLog']) {
            self::write($message);
        } else {
            self::$messages[] = $message;
        }

        if (self::$firstLog && !self::$options['immediatelyWriteLog']) {
            self::$firstLog = false;
            register_shutdown_function([__CLASS__, 'write']);
        }
    }

    /**
     * Write message(s) to the log file
     *
     * @param $message - message for the immediate log
     *
     * @return void
     */
    public static function write($message = null)
    {
        $data = $message ?: self::$messages;
        if (!$message && self::$options['overwriteLogFile'] && self::$options['immediatelyWriteLog']) {
            $data = null;
        }
        if ($data) {
            $logFile = self::formatLogFile();
            if (!file_exists($dir = dirname($logFile)) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
            file_put_contents($logFile, $data, ((!self::$options['overwriteLogFile']) ? FILE_APPEND : 0) | LOCK_EX);
        }
    }

    /**
     * Start capture output buffer
     *
     * @return void
     */
    public static function obStart()
    {
        ob_start();
    }

    /**
     * Clean and get captured output buffer
     *
     * @return void
     */
    public static function obEnd()
    {
        foreach (getallheaders() as $name => $value) {
            echo "$name: $value\n";
        }
        self::log(PHP_EOL . ob_get_clean());
    }

    /**
     * Timer start
     *
     * @return void
     */
    public static function timeStart()
    {
        self::$time = microtime(true);
    }

    /**
     * Timer end
     *
     * @return string time in seconds
     */
    public static function timeEnd()
    {
        return number_format((microtime(true) - self::$time), 2);
    }

    /**
     * Set options for the logger
     *
     * @param string[] $options
     */
    public static function set(array $options)
    {
        self::$options = array_merge(static::$options, $options);
    }

    /**
     * Get options of the logger
     *
     * @return string[]
     */
    public static function get()
    {
        return self::$options;
    }

    /**
     * Format logging message
     *
     * @param $message
     * @param $level
     *
     * @return string
     */
    private static function formatMessage($message, $level)
    {
        return str_replace(['%D%', '%M%', '%L%'], [
            date(self::$options['dateMessageFormat']),
            $message,
            strtoupper($level)
        ], self::$options['logMessageFormat']);
    }

    /**
     * Format log filename
     *
     * @return string
     */
    private static function formatLogFile()
    {
        return self::$options['logFileDir'] . '/' . str_replace('%D%', date(self::$options['dateFileFormat']), self::$options['logFileFormat']);
    }

    /**
     * Static function which is called automatically when there is no such name static method
     *
     * @param $name
     * @param $arguments
     *
     * @return void
     */
    public static function __callStatic($name, $arguments)
    {
        if (defined('self::' . strtoupper($name))) {
            call_user_func(__NAMESPACE__ . '\Log::log', $arguments[0], $name);
        } else {
            throw new BadMethodCallException('BadMethodCallException');
        }
    }
}
