<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Mvc;

use Laminas\ServiceManager\ServiceManager;
use Magento\Framework\Setup\Mvc\ServiceManagerFactory;
use PHPUnit\Framework\TestCase;

class ServiceManagerFactoryTest extends TestCase
{
    public function testGetHasBuildDelegatesToUnderlyingServiceManager(): void
    {
        $laminasSm = new ServiceManager([
            'services' => [
                'alpha' => 'a',
            ],
            'factories' => [
                'beta' => function () {
                    return 'b';
                },
            ],
        ]);

        ServiceManagerFactory::setServiceManager($laminasSm);
        $bridge = new ServiceManagerFactory();

        $this->assertTrue($bridge->has('alpha'));
        $this->assertSame('a', $bridge->get('alpha'));
        $this->assertSame('b', $bridge->build('beta'));
    }
}
