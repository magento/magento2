<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManager\DefinitionFactory;
use Magento\Framework\ObjectManager\DefinitionInterface;
use Magento\Framework\Interception\DefinitionInterface as InterceptionDefinitionInterface;
use Magento\Framework\ObjectManager\RelationsInterface;

class DefinitionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var File|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filesystemDriverMock;

    /**
     * @var DefinitionFactory
     */
    private $definitionFactory;

    protected function setUp(): void
    {
        $this->filesystemDriverMock = $this->createMock(File::class);
        $this->definitionFactory = new DefinitionFactory(
            $this->filesystemDriverMock,
            'generation dir'
        );
    }

    public function testCreateClassDefinition()
    {
        $this->assertInstanceOf(
            DefinitionInterface::class,
            $this->definitionFactory->createClassDefinition()
        );
    }

    public function testCreatePluginDefinition()
    {
        $this->assertInstanceOf(
            InterceptionDefinitionInterface::class,
            $this->definitionFactory->createPluginDefinition()
        );
    }

    public function testCreateRelations()
    {
        $this->assertInstanceOf(
            RelationsInterface::class,
            $this->definitionFactory->createRelations()
        );
    }
}
