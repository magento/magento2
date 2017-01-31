<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\Framework\UrlInterface;
use Magento\Review\Ui\DataProvider\Product\Form\Modifier\Review;

/**
 * Class ReviewTest
 */
class ReviewTest extends AbstractModifierTest
{
    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    protected function setUp()
    {
        parent::setUp();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
    }

    protected function createModel()
    {
        return $this->objectManager->getObject(Review::class, [
            'locator' => $this->locatorMock,
            'urlBuilder' => $this->urlBuilderMock,
        ]);
    }

    public function testModifyMetaToBeEmpty()
    {
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn(0);

        $this->assertSame([], $this->getModel()->modifyMeta([]));
    }

    public function testModifyMeta()
    {
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

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
