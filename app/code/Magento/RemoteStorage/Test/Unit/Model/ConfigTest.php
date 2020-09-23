<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\RemoteStorage\Driver\DriverPool;
use Magento\RemoteStorage\Model\Config;
use PHPUnit\Framework\TestCase;

/**
 * @see Config
 */
class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $configMock->method('getValue')
            ->willReturnMap([
                [DriverPool::PATH_DRIVER, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, DriverPool::REMOTE],
            ]);

        $this->model = new Config($configMock);
    }

    public function testIsEnabled(): void
    {
        self::assertTrue($this->model->isEnabled());
    }
}
