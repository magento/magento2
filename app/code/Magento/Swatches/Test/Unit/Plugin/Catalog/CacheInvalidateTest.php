<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Plugin\Catalog;

class CacheInvalidateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $typeList;

    /**
     * @var \Magento\Swatches\Helper\Data | \PHPUnit\Framework\MockObject\MockObject
     */
    private $swatchHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute | \PHPUnit\Framework\MockObject\MockObject
     */
    private $attribute;

    /**
     * @var \Magento\Swatches\Plugin\Catalog\CacheInvalidate
     */
    private $cacheInvalidate;

    protected function setUp(): void
    {
        $this->typeList = $this->createMock(\Magento\Framework\App\Cache\TypeListInterface::class);
        $this->swatchHelper = $this->createMock(\Magento\Swatches\Helper\Data::class);
        $this->attribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cacheInvalidate = $objectManager->getObject(
            \Magento\Swatches\Plugin\Catalog\CacheInvalidate::class,
            [
                'typeList' => $this->typeList,
                'swatchHelper' => $this->swatchHelper
            ]
        );
    }

    public function testAfterSaveSwatch()
    {
        $this->swatchHelper->expects($this->atLeastOnce())->method('isSwatchAttribute')->with($this->attribute)
            ->willReturn(true);
        $this->typeList->expects($this->at(0))->method('invalidate')->with('block_html');
        $this->typeList->expects($this->at(1))->method('invalidate')->with('collections');
        $this->assertSame($this->attribute, $this->cacheInvalidate->afterSave($this->attribute, $this->attribute));
    }

    public function testAfterSaveNotSwatch()
    {
        $this->swatchHelper->expects($this->atLeastOnce())->method('isSwatchAttribute')->with($this->attribute)
            ->willReturn(false);
        $this->typeList->expects($this->never())->method('invalidate');
        $this->assertSame($this->attribute, $this->cacheInvalidate->afterSave($this->attribute, $this->attribute));
    }
}
