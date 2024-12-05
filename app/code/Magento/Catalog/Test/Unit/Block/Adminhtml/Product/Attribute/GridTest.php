<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Attribute;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Filesystem;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;

class GridTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
    }

    public function testGetRowUrl()
    {
        $attribute = $this->createMock(Attribute::class);
        $attribute->expects($this->once())->method('getAttributeId')->willReturn(2);

        $filesystem = $this->createMock(Filesystem::class);

        $urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'catalog/*/edit',
            ['attribute_id' => 2]
        )->willReturn(
            'catalog/product_attribute/edit/id/2'
        );

        $context = $this->createMock(Context::class);
        $context->expects($this->once())->method('getUrlBuilder')->willReturn($urlBuilder);
        $context->expects($this->any())->method('getFilesystem')->willReturn($filesystem);

        $data = ['context' => $context];

        $helper = new ObjectManager($this);
        /** @var Grid $block */
        $block = $helper->getObject(Grid::class, $data);

        $this->assertEquals('catalog/product_attribute/edit/id/2', $block->getRowUrl($attribute));
    }
}
