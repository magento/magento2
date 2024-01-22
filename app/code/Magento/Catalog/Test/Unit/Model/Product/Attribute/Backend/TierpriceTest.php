<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Tierprice model.
 */
class TierpriceTest extends TestCase
{
    /**
     * @var Tierprice|MockObject
     */
    private $productAttributeBackendTierprice;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $attribute;

    /**
     * @var FormatInterface|MockObject
     */
    private $localeFormat;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var GroupManagementInterface|MockObject
     */
    private $groupManagement;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice
     */
    private $tierprice;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->productAttributeBackendTierprice = $this
            ->getMockBuilder(Tierprice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->addMethods(['isScopeGlobal'])
            ->onlyMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->localeFormat = $this->getMockBuilder(FormatInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->groupManagement = $this->getMockBuilder(GroupManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectHelper = new ObjectManager($this);
        $this->tierprice = $objectHelper->getObject(
            \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice::class,
            [
                '_productAttributeBackendTierprice' => $this->productAttributeBackendTierprice,
                '_attribute' => $this->attribute,
                'localeFormat' => $this->localeFormat,
                '_storeManager' => $this->storeManager,
                '_groupManagement' => $this->groupManagement,
            ]
        );
    }

    /**
     * Test for validate method.
     *
     * @return void
     */
    public function testValidate()
    {
        $attributeName = 'tier_price';
        $tierPrices = [
            [
                'percentage_value' => 15,
                'website_id' => null,
                'cust_group' => null,
                'price_qty' => 1,
            ],
            [
                'price' => 20,
                'website_id' => null,
                'cust_group' => 32000,
                'price_qty' => 1,
            ]
        ];
        $object = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute->expects($this->atLeastOnce())->method('getName')->willReturn($attributeName);
        $object->expects($this->atLeastOnce())->method('getData')->with($attributeName)->willReturn($tierPrices);
        $this->localeFormat->expects($this->atLeastOnce())
            ->method('getNumber')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [15], [20] => 0
            });
        $this->storeManager->expects($this->once())->method('getWebsites')->willReturn([]);
        $this->assertTrue($this->tierprice->validate($object));
    }

    /**
     * Test for validate method with exception.
     *
     * @return void
     */
    public function testValidateWithException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Percentage value must be a number between 0 and 100.');
        $attributeName = 'tier_price';
        $tierPrices = [
            [
                'percentage_value' => -10,
                'website_id' => null,
                'cust_group' => null,
                'price_qty' => 1,
            ]
        ];
        $object = $this->createMock(Product::class);
        $this->attribute->expects($this->atLeastOnce())->method('getName')->willReturn($attributeName);
        $object->expects($this->atLeastOnce())->method('getData')->with($attributeName)->willReturn($tierPrices);
        $this->localeFormat->expects($this->once())->method('getNumber')->with(-10)->willReturnArgument(0);
        $this->assertTrue($this->tierprice->validate($object));
    }

    /**
     * Test for setPriceData method.
     *
     * @return void
     */
    public function testSetPriceData()
    {
        $attributeName = 'tier_price';
        $tierPrices = [
            [
                'price' => 0,
                'all_groups' => 1,
            ],
            [
                'price' => 10,
                'all_groups' => 1,
            ],
            [
                'percentage_value' => 10,
                'all_groups' => 0,
            ],
        ];
        $productPrice = 20;
        $allCustomersGroupId = 32000;
        $finalTierPrices = [
            [
                'price' => 0,
                'all_groups' => 1,
                'website_price' => 0,
                'cust_group' => 32000,
            ],
            [
                'price' => 10,
                'all_groups' => 1,
                'website_price' => 10,
                'cust_group' => 32000,
            ],
            [
                'percentage_value' => 10,
                'all_groups' => 0,
                'price' => 18,
                'website_price' => 18,
            ],
        ];
        $object = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $allCustomersGroup = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->groupManagement
            ->expects($this->exactly(2))
            ->method('getAllCustomersGroup')
            ->willReturn($allCustomersGroup);
        $allCustomersGroup->expects($this->exactly(2))->method('getId')->willReturn($allCustomersGroupId);
        $object->expects($this->once())->method('getPrice')->willReturn($productPrice);
        $this->attribute->expects($this->atLeastOnce())->method('isScopeGlobal')->willReturn(true);
        $object->expects($this->once())->method('getStoreId')->willReturn(null);
        $this->attribute->expects($this->atLeastOnce())->method('getName')->willReturn($attributeName);
        $object->expects($this->atLeastOnce())->method('setData')
            ->willReturnCallback(function ($arg1, $arg2) use ($attributeName, $finalTierPrices, $object) {
                if ($arg1 == $attributeName && $arg2 == $finalTierPrices) {
                    return $object;
                } elseif ($arg1 == $attributeName . '_changed' && $arg2 == 0) {
                    return $object;
                }
            });
        $object->expects($this->atLeastOnce())->method('setOrigData')
            ->willReturnCallback(function ($arg1, $arg2) use ($attributeName, $finalTierPrices, $object) {
                if ($arg1 == $attributeName && $arg2 == $finalTierPrices) {
                    return $object;
                } elseif ($arg1 == $attributeName . '_changed' && $arg2 == 0) {
                    return $object;
                }
            });
        $this->tierprice->setPriceData($object, $tierPrices);
    }
}
