<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Catalog\Model\ResourceModel\Attribute\AttributeConditionsBuilder
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Attribute;

use Magento\Catalog\Model\Entity\Attribute;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Store\Model\Website;
use Magento\Catalog\Model\ResourceModel\Attribute\ConditionBuilder;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Model\Entity\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as CatalogEavAttribute;

/**
 * Class AttributeConditionsBuilderTest
 * @package Magento\Catalog\Test\Unit\Model\ResourceModel\Attribute
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConditionBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param array $scopes
     * @param string $linkFieldValue
     *
     * @dataProvider buildExistingAttributeWebsiteScopeInappropriateAttributeDataProvider
     */
    public function testBuildExistingAttributeWebsiteScopeInappropriateAttribute(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        $linkFieldValue
    ) {
        $storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
            ])
            ->getMock();
        $storeManager->expects($this->never())
            ->method('getStore');

        $conditionsBuilder = new ConditionBuilder($storeManager);
        $result = $conditionsBuilder->buildExistingAttributeWebsiteScope(
            $attribute,
            $metadata,
            $scopes,
            $linkFieldValue
        );

        $this->assertEquals([], $result);
    }

    /**
     * @return array
     */
    public function buildExistingAttributeWebsiteScopeInappropriateAttributeDataProvider()
    {
        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $scopes = [];

        $linkFieldValue = 5;

        return [
            [
                $attribute,
                $metadata,
                $scopes,
                $linkFieldValue,
            ],
        ];
    }

    /**
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param array $scopes
     * @param $linkFieldValue
     *
     * @dataProvider buildExistingAttributeWebsiteScopeStoreScopeNotFoundDataProvider
     */
    public function testBuildExistingAttributeWebsiteScopeStoreScopeNotFound(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        $linkFieldValue
    ) {
        $storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
            ])
            ->getMock();
        $storeManager->expects($this->never())
            ->method('getStore');

        $conditionsBuilder = new ConditionBuilder($storeManager);
        $result = $conditionsBuilder->buildExistingAttributeWebsiteScope(
            $attribute,
            $metadata,
            $scopes,
            $linkFieldValue
        );

        $this->assertEquals([], $result);
    }

    /**
     * @return array
     */
    public function buildExistingAttributeWebsiteScopeStoreScopeNotFoundDataProvider()
    {
        $attribute = $this->getMockBuilder(CatalogEavAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isScopeWebsite',
            ])
            ->getMock();

        $attribute->expects($this->once())
            ->method('isScopeWebsite')
            ->will(
                $this->returnValue(true)
            );

        $metadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $scopes = [];

        $linkFieldValue = 5;

        return [
            [
                $attribute,
                $metadata,
                $scopes,
                $linkFieldValue,
            ],
        ];
    }

    /**
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param StoreInterface $store
     * @param array $scopes
     * @param $linkFieldValue
     *
     * @dataProvider buildExistingAttributeWebsiteScopeStoreWebsiteNotFoundDataProvider
     */
    public function testBuildExistingAttributeWebsiteScopeStoreWebsiteNotFound(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        StoreInterface $store,
        array $scopes,
        $linkFieldValue
    ) {
        $storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
            ])
            ->getMock();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->will(
                $this->returnValue($store)
            );

        $conditionsBuilder = new ConditionBuilder($storeManager);
        $result = $conditionsBuilder->buildExistingAttributeWebsiteScope(
            $attribute,
            $metadata,
            $scopes,
            $linkFieldValue
        );

        $this->assertEquals([], $result);
    }

    /**
     * @return array
     */
    public function buildExistingAttributeWebsiteScopeStoreWebsiteNotFoundDataProvider()
    {
        $attribute = $this->getMockBuilder(CatalogEavAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isScopeWebsite',
            ])
            ->getMock();
        $attribute->expects($this->once())
            ->method('isScopeWebsite')
            ->will(
                $this->returnValue(true)
            );

        $metadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $scope = $this->getMockBuilder(ScopeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIdentifier',
                'getValue',
                'getFallback',
            ])
            ->getMock();
        $scope->expects($this->once())
            ->method('getIdentifier')
            ->will(
                $this->returnValue(Store::STORE_ID)
            );
        $scope->expects($this->once())
            ->method('getValue')
            ->will(
                $this->returnValue(1)
            );
        $scopes = [
            $scope,
        ];

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getWebsite',
            ])
            ->getMock();
        $store->expects($this->once())
            ->method('getWebsite')
            ->will(
                $this->returnValue(false)
            );

        $linkFieldValue = 5;

        return [
            [
                $attribute,
                $metadata,
                $store,
                $scopes,
                $linkFieldValue,
            ],
        ];
    }

    /**
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param StoreInterface $store
     * @param array $scopes
     * @param array $expectedConditions
     * @param $linkFieldValue
     *
     * @dataProvider buildExistingAttributeWebsiteScopeSuccessDataProvider
     */
    public function testBuildExistingAttributeWebsiteScopeSuccess(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        StoreInterface $store,
        array $scopes,
        array $expectedConditions,
        $linkFieldValue
    ) {
        $storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
            ])
            ->getMock();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->will(
                $this->returnValue($store)
            );

        $conditionsBuilder = new ConditionBuilder($storeManager);
        $result = $conditionsBuilder->buildExistingAttributeWebsiteScope(
            $attribute,
            $metadata,
            $scopes,
            $linkFieldValue
        );

        $this->assertEquals($expectedConditions, $result);
    }

    /**
     * @return array
     */
    public function buildExistingAttributeWebsiteScopeSuccessDataProvider()
    {
        $attribute = $this->getValidAttributeMock();

        $dbAdapater = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'quoteIdentifier',
            ])
            ->getMock();
        $dbAdapater->expects($this->exactly(3))
            ->method('quoteIdentifier')
            ->will(
                $this->returnCallback(function ($input) {
                    return sprintf('`%s`', $input);
                })
            );

        $metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getLinkField',
                'getEntityConnection',
            ])
            ->getMock();
        $metadata->expects($this->once())
            ->method('getLinkField')
            ->will(
                $this->returnValue('entity_id')
            );
        $metadata->expects($this->exactly(3))
            ->method('getEntityConnection')
            ->will(
                $this->returnValue($dbAdapater)
            );

        $scopes = [
            $this->getValidScopeMock(),
        ];

        $store = $this->getValidStoreMock();

        $expectedConditions = [
            [
                'entity_id = ?' => 5,
                'attribute_id = ?' => 12,
                '`store_id` = ?' => 1,
            ],
            [
                'entity_id = ?' => 5,
                'attribute_id = ?' => 12,
                '`store_id` = ?' => 2,
            ],
            [
                'entity_id = ?' => 5,
                'attribute_id = ?' => 12,
                '`store_id` = ?' => 3,
            ],
        ];

        $linkFieldValue = 5;

        return [
            [
                $attribute,
                $metadata,
                $store,
                $scopes,
                $expectedConditions,
                $linkFieldValue,
            ],
        ];
    }

    /**
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param array $scopes
     * @param string $linkFieldValue
     *
     * @dataProvider buildNewAttributeWebsiteScopeUnappropriateAttributeDataProvider
     */
    public function testBuildNewAttributeWebsiteScopeUnappropriateAttribute(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        $linkFieldValue
    ) {
        $storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
            ])
            ->getMock();
        $storeManager->expects($this->never())
            ->method('getStore');

        $conditionsBuilder = new ConditionBuilder($storeManager);
        $result = $conditionsBuilder->buildNewAttributesWebsiteScope(
            $attribute,
            $metadata,
            $scopes,
            $linkFieldValue
        );

        $this->assertEquals([], $result);
    }

    /**
     * @return array
     */
    public function buildNewAttributeWebsiteScopeUnappropriateAttributeDataProvider()
    {
        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $scopes = [];

        $linkFieldValue = 5;

        return [
            [
                $attribute,
                $metadata,
                $scopes,
                $linkFieldValue,
            ],
        ];
    }

    /**
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param StoreInterface $store
     * @param array $scopes
     * @param array $expectedConditions
     * @param $linkFieldValue
     *
     * @dataProvider buildNewAttributeWebsiteScopeSuccessDataProvider
     */
    public function testBuildNewAttributeWebsiteScopeSuccess(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        StoreInterface $store,
        array $scopes,
        array $expectedConditions,
        $linkFieldValue
    ) {
        $storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
            ])
            ->getMock();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->will(
                $this->returnValue($store)
            );

        $conditionsBuilder = new ConditionBuilder($storeManager);
        $result = $conditionsBuilder->buildNewAttributesWebsiteScope(
            $attribute,
            $metadata,
            $scopes,
            $linkFieldValue
        );

        $this->assertEquals($expectedConditions, $result);
    }

    /**
     * @return array
     */
    public function buildNewAttributeWebsiteScopeSuccessDataProvider()
    {
        $attribute = $this->getValidAttributeMock();

        $metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getLinkField',
            ])
            ->getMock();
        $metadata->expects($this->once())
            ->method('getLinkField')
            ->will(
                $this->returnValue('entity_id')
            );

        $scopes = [
            $this->getValidScopeMock(),
        ];

        $store = $this->getValidStoreMock();

        $expectedConditions = [
            [
                'entity_id' => 5,
                'attribute_id' => 12,
                'store_id' => 1,
            ],
            [
                'entity_id' => 5,
                'attribute_id' => 12,
                'store_id' => 2,
            ],
            [
                'entity_id' => 5,
                'attribute_id' => 12,
                'store_id' => 3,
            ],
        ];

        $linkFieldValue = 5;

        return [
            [
                $attribute,
                $metadata,
                $store,
                $scopes,
                $expectedConditions,
                $linkFieldValue,
            ],
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getValidAttributeMock()
    {
        $attribute = $this->getMockBuilder(CatalogEavAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isScopeWebsite',
                'getAttributeId',
            ])
            ->getMock();
        $attribute->expects($this->once())
            ->method('isScopeWebsite')
            ->will(
                $this->returnValue(true)
            );
        $attribute->expects($this->once())
            ->method('getAttributeId')
            ->will(
                $this->returnValue(12)
            );

        return $attribute;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getValidStoreMock()
    {
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStoreIds',
            ])
            ->getMock();
        $website->expects($this->once())
            ->method('getStoreIds')
            ->will(
                $this->returnValue([
                    1,
                    2,
                    3,
                ])
            );

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getWebsite',
            ])
            ->getMock();
        $store->expects($this->once())
            ->method('getWebsite')
            ->will(
                $this->returnValue($website)
            );

        return $store;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getValidScopeMock()
    {
        $scope = $this->getMockBuilder(ScopeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIdentifier',
                'getValue',
                'getFallback',
            ])
            ->getMock();
        $scope->expects($this->once())
            ->method('getIdentifier')
            ->will(
                $this->returnValue(Store::STORE_ID)
            );
        $scope->expects($this->once())
            ->method('getValue')
            ->will(
                $this->returnValue(1)
            );

        return $scope;
    }
}
