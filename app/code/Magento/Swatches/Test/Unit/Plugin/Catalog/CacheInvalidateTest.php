<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Plugin\Catalog;

class CacheInvalidateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $typeList;

    /**
     * @var \Magento\Swatches\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    private $swatchHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute | \PHPUnit_Framework_MockObject_MockObject
     */
    private $attribute;

    /**
     * @var \Magento\Swatches\Plugin\Catalog\CacheInvalidate
     */
    private $cacheInvalidate;

    protected function setUp()
    {
        $this->typeList = $this->getMock(
            \Magento\Framework\App\Cache\TypeListInterface::class,
            [],
            [],
            '',
            false
        );
        $this->swatchHelper = $this->getMock(
            \Magento\Swatches\Helper\Data::class,
            [],
            [],
            '',
            false
        );
        $this->attribute = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            [],
            [],
            '',
            false
        );

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
