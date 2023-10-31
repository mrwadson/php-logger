<?php

use mrwadson\logger\Log;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testFileLogExists()
    {
        Log::options([
            'log_dir' => __DIR__ . '/../log',
            'overwrite_log_file' => true,
            'immediately_write_log' => true
        ]);

        Log::log('Test INFO message');
        $logFile = __DIR__ . '/../log/log-' . date('Y-m-d') . '.log';

        $this->assertFileExists($logFile);
    }

    public function testIsTimerWork()
    {
        Log::options([
            'log_dir' => __DIR__ . '/../log',
            'log_file_format' => 'log-timer-%D%.log',
            'overwrite_log_file' => true
        ]);
        Log::timeStart();
        sleep(2);
        $time = Log::timeEnd();
        Log::alert($time);

        $this->assertNotEmpty($time);
    }

    public function testIsBufferWork()
    {
        Log::options([
            'log_dir' => __DIR__ . '/../log',
            'log_file_format' => 'log-buffer-%D%.log',
        ]);
        Log::obStart();
        echo 'Test for buffer capture.';
        sleep(1);
        Log::obEnd();
        Log::critical('Buffer test end.');

        $this->expectNotToPerformAssertions();
    }
}
