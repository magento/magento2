<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Plugin\Catalog;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Plugin\Catalog\CacheInvalidate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CacheInvalidateTest extends TestCase
{
    /**
     * @var TypeListInterface|MockObject
     */
    private $typeList;

    /**
     * @var Data|MockObject
     */
    private $swatchHelper;

    /**
     * @var Attribute|MockObject
     */
    private $attribute;

    /**
     * @var CacheInvalidate
     */
    private $cacheInvalidate;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->typeList = $this->getMockForAbstractClass(TypeListInterface::class);
        $this->swatchHelper = $this->createMock(Data::class);
        $this->attribute = $this->createMock(Attribute::class);

        $objectManager = new ObjectManager($this);
        $this->cacheInvalidate = $objectManager->getObject(
            CacheInvalidate::class,
            [
                'typeList' => $this->typeList,
                'swatchHelper' => $this->swatchHelper
            ]
        );
    }

    /**
     * @return void
     */
    public function testAfterSaveSwatch(): void
    {
        $this->swatchHelper->expects($this->atLeastOnce())->method('isSwatchAttribute')->with($this->attribute)
            ->willReturn(true);
        $this->typeList
            ->method('invalidate')
            ->willReturnCallback(function ($arg1) use (&$callCount) {
                if ($arg1 == 'block_html' || $arg1 == 'collections') {
                    return null;
                }
            });
        $this->assertSame($this->attribute, $this->cacheInvalidate->afterSave($this->attribute, $this->attribute));
    }

    /**
     * @return void
     */
    public function testAfterSaveNotSwatch(): void
    {
        $this->swatchHelper->expects($this->atLeastOnce())->method('isSwatchAttribute')->with($this->attribute)
            ->willReturn(false);
        $this->typeList->expects($this->never())->method('invalidate');
        $this->assertSame($this->attribute, $this->cacheInvalidate->afterSave($this->attribute, $this->attribute));
    }
}
