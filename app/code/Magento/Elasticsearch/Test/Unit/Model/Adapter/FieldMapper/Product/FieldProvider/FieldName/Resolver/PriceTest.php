<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Store\Api\Data\StoreInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\Price
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
            ->setMethods(['getCustomerGroupId'])
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManagerHelper($this);

        $this->resolver = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\Price::class,
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
            ->setMethods(['getAttributeCode'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->customerSession->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(1);
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId'])
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
    public function getFieldNameProvider()
    {
        return [
            ['price', [], 'price_1_2'],
            ['price', ['customerGroupId' => 2, 'websiteId' => 3], 'price_2_3'],
            ['price', ['customerGroupId' => 2], 'price_2_2'],
            ['sku', ['customerGroupId' => 2], ''],
        ];
    }
}
