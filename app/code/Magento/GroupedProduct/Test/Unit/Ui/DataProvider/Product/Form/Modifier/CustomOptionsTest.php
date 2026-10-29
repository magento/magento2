<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTestCase;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\GroupedProduct\Ui\DataProvider\Product\Form\Modifier\CustomOptions as CustomOptionsModifier;

class CustomOptionsTest extends AbstractModifierTestCase
{
    /**
     * Override parent setUp to recreate arrayManagerMock without willReturnArgument
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Recreate arrayManagerMock to allow tracking of findPath calls
        $this->arrayManagerMock = $this->createMock(ArrayManager::class);
        
        // Only configure the methods actually used by CustomOptions
        $this->arrayManagerMock->method('remove')
            ->willReturnCallback(function ($path, $data) {
                unset($path); // Mark as intentionally unused
                return $data;
            });
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(
            CustomOptionsModifier::class,
            [
                'locator' => $this->locatorMock,
                'arrayManager' => $this->arrayManagerMock
            ]
        );
    }

    public function testModifyDataNotGrouped()
    {
        $data = ['data'];

        $this->productMock->method('getTypeId')->willReturn('simple');
        $this->arrayManagerMock->expects($this->never())
            ->method('findPath');

        $this->assertSame($data, $this->getModel()->modifyData($data));
    }

    public function testModifyData()
    {
        $data = ['data'];

        $this->productMock->method('getTypeId')->willReturn(CustomOptionsModifier::PRODUCT_TYPE_GROUPED);
        $this->arrayManagerMock->expects($this->once())
            ->method('findPath');

        $this->assertSame($data, $this->getModel()->modifyData($data));
    }
}
