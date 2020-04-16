<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Config;

use Magento\Payment\Gateway\Config\ConfigValueHandler;
use Magento\Payment\Gateway\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigValueHandlerTest extends TestCase
{
    /** @var ConfigValueHandler */
    protected $model;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $configMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
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
