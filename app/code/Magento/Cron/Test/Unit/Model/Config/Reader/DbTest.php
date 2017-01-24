<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model\Config\Reader;

use Magento\Framework\App\Config;
use Magento\GoogleAdwords\Block\Code;

/**
 * Test reading for cron parameters from data base storage
 *
 * @package Magento\Cron\Test\Unit\Model\Config\Reader
 */
class DbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Cron\Model\Config\Converter\Db|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converter;

    /**
     * @var \Magento\Cron\Model\Config\Reader\Db
     */
    protected $_reader;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_converter = new \Magento\Cron\Model\Config\Converter\Db();
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
        $this->config->expects($this->once())->method('get')->with('system/default')->will($this->returnValue($data));
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
