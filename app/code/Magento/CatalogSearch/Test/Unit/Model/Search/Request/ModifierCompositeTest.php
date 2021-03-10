<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search\Request;

use Magento\CatalogSearch\Model\Search\Request\ModifierComposite;
use Magento\CatalogSearch\Model\Search\Request\ModifierInterface;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test composite search requests modifier
 */
class ModifierCompositeTest extends TestCase
{
    /**
     * @var ModifierInterface|MockObject
     */
    private $modifier1;

    /**
     * @var ModifierInterface|MockObject
     */
    private $modifier2;

    /**
     * @var ModifierComposite
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->modifier1 = $this->getMockForAbstractClass(ModifierInterface::class);
        $this->modifier2 = $this->getMockForAbstractClass(ModifierInterface::class);
        $this->model = new ModifierComposite(
            [
                $this->modifier1,
                $this->modifier2
            ]
        );
    }

    /**
     * Test that all modifiers are executed
     */
    public function testModify(): void
    {
        $requests = ['a', 'b', 'c'];
        $this->modifier1->expects($this->once())
            ->method('modify')
            ->with($requests)
            ->willReturn(['a', 'b', 'c', 'd']);

        $this->modifier2->expects($this->once())
            ->method('modify')
            ->with(['a', 'b', 'c', 'd'])
            ->willReturn(['a', 'c', 'd']);

        $this->assertEquals(['a', 'c', 'd'], $this->model->modify($requests));
    }

    /**
     * Test that exception is thrown if modifier is not instance of ModifierInterface
     */
    public function testInvalidModifier(): void
    {
        $exception = new \InvalidArgumentException(
            'Magento\Framework\DataObject must implement Magento\CatalogSearch\Model\Search\Request\ModifierInterface'
        );
        $this->expectExceptionObject($exception);
        $this->model = new ModifierComposite(
            [
                new DataObject()
            ]
        );
    }
}
