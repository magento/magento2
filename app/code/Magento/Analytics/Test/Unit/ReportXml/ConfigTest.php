<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml;

use Magento\Analytics\ReportXml\Config;
use Magento\Analytics\ReportXml\Config\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ConfigTest
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->dataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->config = $this->objectManagerHelper->getObject(
            Config::class,
            [
                'data' => $this->dataMock,
            ]
        );
    }

    public function testGet()
    {
        $queryName = 'query string';
        $queryResult = [ 'query' => 1 ];

        $this->dataMock
            ->expects($this->once())
            ->method('get')
            ->with($queryName)
            ->willReturn($queryResult);

        $this->assertSame($queryResult, $this->config->get($queryName));
    }
}
