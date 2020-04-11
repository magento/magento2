<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model\Config\Reader;

use Magento\Cron\Model\Config\Converter\Db;
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
    protected $config;

    /**
     * @var Db|MockObject
     */
    protected $_converter;

    /**
     * @var \Magento\Cron\Model\Config\Reader\Db
     */
    protected $_reader;

    /**
     * Initialize parameters
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_converter = new Db();
        $this->_reader = new \Magento\Cron\Model\Config\Reader\Db($this->config, $this->_converter);
    }

    /**
     * Testing method execution
     */
    public function testGet()
    {
        $job1 = ['schedule' => ['cron_expr' => '* * * * *']];
        $job2 = ['schedule' => ['cron_expr' => '1 1 1 1 1']];
        $data = ['crontab' => ['default' => ['jobs' => ['job1' => $job1, 'job2' => $job2]]]];
        $this->config->expects($this->once())
            ->method('get')
            ->with('system', 'default')
            ->will($this->returnValue($data));
        $expected = [
            'default' => [
                'job1' => ['schedule' => $job1['schedule']['cron_expr']],
                'job2' => ['schedule' => $job2['schedule']['cron_expr']],
            ],
        ];

        $result = $this->_reader->get();
        $this->assertEquals($expected['default']['job1']['schedule'], $result['default']['job1']['schedule']);
        $this->assertEquals($expected['default']['job2']['schedule'], $result['default']['job2']['schedule']);
    }
}
