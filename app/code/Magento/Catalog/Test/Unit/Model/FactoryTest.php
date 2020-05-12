<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Model\Factory;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Option
     */
    protected $model;

    /**
     * @var Factory
     */
    protected $factory;

    public function testCreate()
    {
        $this->assertInstanceOf(Option::class, $this->factory->create('model', []));
    }

    public function testExceptionCreate()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->factory->create('null', []);
    }

    protected function setUp(): void
    {
        $this->model = $this->createMock(Option::class);

        $this->setObjectManager();

        $this->factory = new Factory($this->objectManager);
    }

    protected function setObjectManager()
    {
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->objectManager
            ->expects($this->any())
            ->method('create')
            ->with($this->logicalOr($this->equalTo('model'), $this->equalTo('null')), [])
            ->willReturnCallback(function ($className) {
                $returnValue = null;
                if ($className == 'model') {
                    $returnValue = $this->model;
                }
                return $returnValue;
            });
    }
}
