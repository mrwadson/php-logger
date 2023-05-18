<?php

use mrwadson\logger\Log;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testFileLogExists()
    {
        Log::options([
            'log_dir' => __DIR__ . '/../log',
            'immediately_write_log' => true
        ]);

        Log::log('Test info message');
        $logFile = __DIR__ . '/../log/log-' . date('Y-m-d') . '.log';
        $this->assertFileExists($logFile);
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function testBadMethodCallException()
    {
        $this->expectException(BadMethodCallException::class);
        Log::message();
    }
}
