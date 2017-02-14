<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\Config;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataInterfaceMock;

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
        $this->dataInterfaceMock = $this->getMockBuilder(DataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->config = $this->objectManagerHelper->getObject(
            Config::class,
            [
                'data' => $this->dataInterfaceMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $key = 'configKey';
        $defaultValue = 'mock';
        $configValue = 'emptyString';

        $this->dataInterfaceMock
            ->expects($this->once())
            ->method('get')
            ->with($key, $defaultValue)
            ->willReturn($configValue);

        $this->assertSame($configValue, $this->config->get($key, $defaultValue));
    }
}
