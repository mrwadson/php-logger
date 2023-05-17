<?php

namespace mrwadson\logger;

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
        'log_dir' => __DIR__ . '/../log', // dir contains your logs
        'log_file_format' => 'log-%D%.log', // %DIR% - log file dir, %D% - date
        'log_message_format' => '[%D%]: %L% - %M%', // %D% - date, %L% - log level, %M% - message
        'log_array_in_one_row' => false,
        'overwrite_log_file' => false,
        'immediately_write_log' => false,
        'date_file_format' => 'Y-m-d',
        'date_message_format' => 'Y-m-d H:i:s'
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
     * Set or Get options for the logger
     *
     * @param string[] $options
     * @return void | array
     */
    public static function options($options)
    {
        if (!is_array($options)) {
            return self::$options;
        }
        self::$options = array_merge(static::$options, $options);
    }

    /**
     * Prepare log message for further logging
     *
     * @param $message - can be string, or an array
     *
     * @return void
     */
    public static function log($message, $level = self::INFO)
    {
        if (is_array($message)) {
            $message = rtrim(print_r($message, true), PHP_EOL);
            if (self::$options['log_array_in_one_row']) {
                $message = str_replace(['    ', PHP_EOL], '', $message);
            }
        }

        $message = self::formatMessage($message, $level) . PHP_EOL;

        if (self::$options['immediately_write_log']) {
            self::write($message);
        } else {
            self::$messages[] = $message;
        }

        if (self::$firstLog && !self::$options['immediately_write_log']) {
            self::$firstLog = false;
            register_shutdown_function([__CLASS__, 'write']);
        }
    }

    /**
     * Write a message(s) to the log file
     *
     * @param $message - message for the immediate log
     *
     * @return void
     */
    public static function write($message = null)
    {
        $data = $message ?: self::$messages;
        if (!$message && self::$options['overwrite_log_file'] && self::$options['immediately_write_log']) {
            $data = null;
        }
        if ($data) {
            $logFile = self::formatLogFile();
            if (!file_exists($dir = dirname($logFile)) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
            file_put_contents($logFile, $data, ((!self::$options['overwrite_log_file']) ? FILE_APPEND : 0) | LOCK_EX);
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
            date(self::$options['date_message_format']),
            $message,
            strtoupper($level)
        ], self::$options['log_message_format']);
    }

    /**
     * Format log filename
     *
     * @return string
     */
    private static function formatLogFile()
    {
        return self::$options['log_dir'] . '/' . str_replace('%D%', date(self::$options['date_file_format']), self::$options['log_file_format']);
    }

    /**
     * Static function, which is called automatically when there is no such name static method.
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
