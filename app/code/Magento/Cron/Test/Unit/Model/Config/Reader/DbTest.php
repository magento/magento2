<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cron\Test\Unit\Model\Config\Reader;

use Magento\Cron\Model\Config\Converter\Db as DbConfigConverter;
use Magento\Cron\Model\Config\Reader\Db as DbConfigReader;
use Magento\Framework\App\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test reading for cron parameters from data base storage
 */
class DbTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var DbConfigConverter
     */
    private $configConverter;

    /**
     * @var DbConfigReader
     */
    private $reader;

    /**
     * Initialize parameters
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configConverter = new DbConfigConverter();
        $this->reader = new DbConfigReader($this->configMock, $this->configConverter);
    }

    /**
     * Testing method execution
     */
    public function testGet()
    {
        $job1 = ['schedule' => ['cron_expr' => '* * * * *']];
        $job2 = ['schedule' => ['cron_expr' => '1 1 1 1 1']];
        $data = ['crontab' => ['default' => ['jobs' => ['job1' => $job1, 'job2' => $job2]]]];
        $this->configMock->expects($this->once())
            ->method('get')
            ->with('system', 'default')
            ->willReturn($data);
        $expected = [
            'default' => [
                'job1' => ['schedule' => $job1['schedule']['cron_expr']],
                'job2' => ['schedule' => $job2['schedule']['cron_expr']],
            ],
        ];

        $result = $this->reader->get();
        $this->assertEquals($expected['default']['job1']['schedule'], $result['default']['job1']['schedule']);
        $this->assertEquals($expected['default']['job2']['schedule'], $result['default']['job2']['schedule']);
    }
}
