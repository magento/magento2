<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Config\Reader;

use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Framework\ObjectManager\Config\Reader\DomFactory;
use Magento\Framework\ObjectManager\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DomFactoryTest extends TestCase
{
    /**
     * @var DomFactory
     */
    protected $_factory;

    /**
     * @var MockObject
     */
    protected $_object;

    /**
     * @var ObjectManager|MockObject
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_object = $this->createMock(Dom::class);
        $this->_objectManager =
            $this->createPartialMock(ObjectManager::class, ['create']);
        $this->_factory = new DomFactory($this->_objectManager);
    }

    public function testCreate()
    {
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with(Dom::class)
            ->willReturn($this->_object);

        $this->_factory->create([1]);
    }
}
