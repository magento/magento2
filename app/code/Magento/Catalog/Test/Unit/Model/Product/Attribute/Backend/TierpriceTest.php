<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

/**
 * Unit test for Tierprice model.
 */
class TierpriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $productAttributeBackendTierprice;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attribute;

    /**
     * @var \Magento\Framework\Locale\FormatInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeFormat;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
    {
        $this->productAttributeBackendTierprice = $this
            ->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice::class)
            ->disableOriginalConstructor()->getMock();
        $this->attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->setMethods(['getName', 'isScopeGlobal'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->localeFormat = $this->getMockBuilder(\Magento\Framework\Locale\FormatInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->groupManagement = $this->getMockBuilder(\Magento\Customer\Api\GroupManagementInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
        $object = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()->getMock();
        $this->attribute->expects($this->atLeastOnce())->method('getName')->willReturn($attributeName);
        $object->expects($this->atLeastOnce())->method('getData')->with($attributeName)->willReturn($tierPrices);
        $this->localeFormat->expects($this->atLeastOnce())
            ->method('getNumber')->withConsecutive([15], [20])->willReturnArgument(0);
        $this->storeManager->expects($this->once())->method('getWebsites')->willReturn([]);
        $this->assertTrue($this->tierprice->validate($object));
    }

    /**
     * Test for validate method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Percentage value must be a number between 0 and 100.
     */
    public function testValidateWithException()
    {
        $attributeName = 'tier_price';
        $tierPrices = [
            [
                'percentage_value' => -10,
                'website_id' => null,
                'cust_group' => null,
                'price_qty' => 1,
            ]
        ];
        $object = $this->createMock(\Magento\Catalog\Model\Product::class);
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
        $object = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()->getMock();
        $allCustomersGroup = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->groupManagement->expects($this->once())->method('getAllCustomersGroup')->willReturn($allCustomersGroup);
        $allCustomersGroup->expects($this->once())->method('getId')->willReturn($allCustomersGroupId);
        $object->expects($this->once())->method('getPrice')->willReturn($productPrice);
        $this->attribute->expects($this->atLeastOnce())->method('isScopeGlobal')->willReturn(true);
        $object->expects($this->once())->method('getStoreId')->willReturn(null);
        $this->attribute->expects($this->atLeastOnce())->method('getName')->willReturn($attributeName);
        $object->expects($this->atLeastOnce())->method('setData')
            ->withConsecutive(
                [$attributeName, $finalTierPrices],
                [$attributeName . '_changed', 0]
            )->willReturnSelf();
        $object->expects($this->atLeastOnce())->method('setOrigData')
            ->withConsecutive(
                [$attributeName, $finalTierPrices],
                [$attributeName . '_changed', 0]
            )->willReturnSelf();
        $this->tierprice->setPriceData($object, $tierPrices);
    }
}
