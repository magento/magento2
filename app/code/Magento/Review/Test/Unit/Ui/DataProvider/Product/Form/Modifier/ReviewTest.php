<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\UrlInterface;
use Magento\Review\Ui\DataProvider\Product\Form\Modifier\Review;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use PHPUnit\Framework\MockObject\MockObject;

class ReviewTest extends AbstractModifierTest
{
    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var MockObject
     */
    private $moduleManagerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->moduleManagerMock = $this->createMock(ModuleManager::class);
    }

    /**
     * @return ModifierInterface
     */
    protected function createModel()
    {
        $model = $this->objectManager->getObject(
            Review::class,
            [
                'locator' => $this->locatorMock,
                'urlBuilder' => $this->urlBuilderMock,
            ]
        );

        $reviewClass = new \ReflectionClass(Review::class);
        $moduleManagerProperty = $reviewClass->getProperty('moduleManager');
        $moduleManagerProperty->setAccessible(true);
        $moduleManagerProperty->setValue(
            $model,
            $this->moduleManagerMock
        );

        return $model;
    }

    public function testModifyMetaDoesNotAddReviewSectionForNewProduct()
    {
        $this->productMock->expects($this->once())
            ->method('getId');

        $this->assertSame([], $this->getModel()->modifyMeta([]));
    }

    public function testModifyMetaDoesNotAddReviewSectionIfReviewModuleOutputIsDisabled()
    {
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->moduleManagerMock->expects($this->any())
            ->method('isOutputEnabled')
            ->with('Magento_Review')
            ->willReturn(false);

        $this->assertSame([], $this->getModel()->modifyMeta([]));
    }

    public function testModifyMetaAddsReviewSectionForExistingProductIfReviewModuleOutputIsEnabled()
    {
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->moduleManagerMock->expects($this->any())
            ->method('isOutputEnabled')
            ->with('Magento_Review')
            ->willReturn(true);

        $this->assertArrayHasKey(Review::GROUP_REVIEW, $this->getModel()->modifyMeta([]));
    }

    public function testModifyData()
    {
        $productId = 1;

        $this->productMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn($productId);

        $this->assertArrayHasKey($productId, $this->getModel()->modifyData([]));
        $this->assertArrayHasKey(Review::DATA_SOURCE_DEFAULT, $this->getModel()->modifyData([])[$productId]);
        $this->assertArrayHasKey(
            'current_product_id',
            $this->getModel()->modifyData([])[$productId][Review::DATA_SOURCE_DEFAULT]
        );
    }
}
