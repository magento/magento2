<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\Price;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD)
 */
class PriceTest extends TestCase
{
    /**
     * @var Price
     */
    private $resolver;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->customerSession = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCustomerGroupId'])
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManagerHelper($this);

        $this->resolver = $objectManager->getObject(
            Price::class,
            [
                'customerSession' => $this->customerSession,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    /**
     * @dataProvider getFieldNameProvider
     * @param $attributeCode
     * @param $context
     * @param $expected
     * @return void
     */
    public function testGetFieldName($attributeCode, $context, $expected)
    {
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttributeCode'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->customerSession->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(1);
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getWebsiteId'])
            ->getMockForAbstractClass();
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(2);
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $this->assertEquals(
            $expected,
            $this->resolver->getFieldName($attributeMock, $context)
        );
    }

    /**
     * @return array
     */
    public static function getFieldNameProvider()
    {
        return [
            ['price', [], 'price_1_2'],
            ['price', ['customerGroupId' => 2, 'websiteId' => 3], 'price_2_3'],
            ['price', ['customerGroupId' => 2], 'price_2_2'],
            ['sku', ['customerGroupId' => 2], ''],
        ];
    }
}
