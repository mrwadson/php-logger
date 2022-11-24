<?php

use App\Log;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testFileLogExists()
    {
        Log::set(['immediatelyWriteLog' => true]);
        Log::log('Test info message');
        $logFile = __DIR__ . '/../log/log-' . date('Y-m-d') . '.log';
        $this->assertFileExists($logFile);
    }

    public function testBadMethodCallException()
    {
        $this->expectException(BadMethodCallException::class);
        Log::message();
    }
}
