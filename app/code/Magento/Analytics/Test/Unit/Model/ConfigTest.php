<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\Config;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends TestCase
{
    /**
     * @var DataInterface|MockObject
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
    protected function setUp(): void
    {
        $this->dataInterfaceMock = $this->getMockForAbstractClass(DataInterface::class);

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
