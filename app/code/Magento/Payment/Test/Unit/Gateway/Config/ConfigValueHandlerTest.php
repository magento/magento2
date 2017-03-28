<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Config;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Config\ConfigValueHandler;

/**
 * Class ConfigValueHandlerTest
 */
class ConfigValueHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ConfigValueHandler */
    protected $model;

    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(\Magento\Payment\Gateway\ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->model = new ConfigValueHandler($this->configMock);
    }

    public function testHandle()
    {
        $field = 'field';
        $storeId = 1;
        $expected = 'some value';

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with($field, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->handle(['field' => $field], $storeId));
    }

    public function testHandleWithoutStoreId()
    {
        $field = 'field';
        $expected = 'some value';

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with($field, null)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->handle(['field' => $field]));
    }
}
