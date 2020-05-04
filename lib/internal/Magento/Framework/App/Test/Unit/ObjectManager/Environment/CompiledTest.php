<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\ObjectManager\Environment;

use Magento\Framework\App\EnvironmentFactory;
use Magento\Framework\App\ObjectManager\Environment\Compiled;
use PHPUnit\Framework\TestCase;

class CompiledTest extends TestCase
{
    /**
     * @var Compiled
     */
    protected $_compiled;

    protected function setUp(): void
    {
        $envFactoryMock = $this->createMock(EnvironmentFactory::class);
        $this->_compiled = new CompiledTesting($envFactoryMock);
    }

    public function testGetMode()
    {
        $this->assertEquals(Compiled::MODE, $this->_compiled->getMode());
    }

    public function testGetObjectManagerFactory()
    {
        $this->assertInstanceOf(
            \Magento\Framework\ObjectManager\Factory\Compiled::class,
            $this->_compiled->getObjectManagerFactory(['shared_instances' => []])
        );
    }
}
