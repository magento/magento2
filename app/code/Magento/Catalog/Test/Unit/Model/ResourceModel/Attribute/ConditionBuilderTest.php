<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Catalog\Model\ResourceModel\Attribute\AttributeConditionsBuilder
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Attribute;

use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\ResourceModel\Attribute\ConditionBuilder;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as CatalogEavAttribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Entity\ScopeInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConditionBuilderTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var ConditionBuilder
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore'])
            ->getMock();
        $this->model = new ConditionBuilder($this->storeManagerMock);
    }

    /**
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param array $scopes
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     * @dataProvider buildExistingAttributeWebsiteScopeInappropriateAttributeDataProvider
     */
    public function testBuildExistingAttributeWebsiteScopeInappropriateAttribute(
        AbstractAttribute       $attribute,
        EntityMetadataInterface $metadata,
        array                   $scopes,
        string                  $linkFieldValue
    ) {
        $this->storeManagerMock->expects($this->never())
            ->method('getStore');
        $result = $this->model->buildExistingAttributeWebsiteScope(
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
            ->getMockForAbstractClass();

        $scopes = [];

        $linkFieldValue = '5';

        return [
            [
                $attribute,
                $metadata,
                $scopes,
                $linkFieldValue
            ],
        ];
    }

    /**
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param array $scopes
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     * @dataProvider buildExistingAttributeWebsiteScopeStoreScopeNotFoundDataProvider
     */
    public function testBuildExistingAttributeWebsiteScopeStoreScopeNotFound(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        string $linkFieldValue
    ) {
        $this->storeManagerMock->expects($this->any())
            ->method('getStore');
        $result = $this->model->buildExistingAttributeWebsiteScope(
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
            ->onlyMethods([
                'isScopeWebsite',
            ])
            ->getMock();

        $attribute->expects($this->never())
            ->method('isScopeWebsite')
            ->willReturn(
                true
            );

        $metadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $scopes = [];

        $linkFieldValue = '5';

        return [
            [
                $attribute,
                $metadata,
                $scopes,
                $linkFieldValue
            ],
        ];
    }

    /**
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param StoreInterface $store
     * @param array $scopes
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     * @dataProvider buildExistingAttributeWebsiteScopeStoreWebsiteNotFoundDataProvider
     */
    public function testBuildExistingAttributeWebsiteScopeStoreWebsiteNotFound(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        StoreInterface $store,
        array $scopes,
        string $linkFieldValue
    ) {
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn(
                $store
            );
        $result = $this->model->buildExistingAttributeWebsiteScope(
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
            ->onlyMethods([
                'isScopeWebsite',
            ])
            ->getMock();
        $attribute->expects($this->never())
            ->method('isScopeWebsite')
            ->willReturn(
                true
            );

        $metadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $scope = $this->getMockBuilder(ScopeInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getIdentifier',
                'getValue',
                'getFallback',
            ])
            ->getMockForAbstractClass();
        $scope->expects($this->any())
            ->method('getIdentifier')
            ->willReturn(
                Store::STORE_ID
            );
        $scope->expects($this->any())
            ->method('getValue')
            ->willReturn(
                1
            );
        $scopes = [
            $scope,
        ];

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getWebsite',
            ])
            ->getMock();
        $store->expects($this->any())
            ->method('getWebsite')
            ->willReturn(
                false
            );

        $linkFieldValue = '5';

        return [
            [
                $attribute,
                $metadata,
                $store,
                $scopes,
                $linkFieldValue
            ],
        ];
    }

    /**
     * Test case for build existing attribute when website scope store with storeIds  empty
     *
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param StoreInterface $store
     * @param array $scopes
     * @param array $expectedConditions
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     * @dataProvider buildExistingAttributeWebsiteScopeStoreWithStoreIdsEmpty
     */
    public function testBuildExistingAttributeWebsiteScopeStoreWithStoreIdsEmpty(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        StoreInterface $store,
        array $scopes,
        array $expectedConditions,
        string $linkFieldValue
    ) {
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $result = $this->model->buildExistingAttributeWebsiteScope(
            $attribute,
            $metadata,
            $scopes,
            $linkFieldValue
        );

        $this->assertEquals($expectedConditions, $result);
    }

    /**
     * Data provider for attribute website scope store with storeIds  empty
     *
     * @return array
     */
    public function buildExistingAttributeWebsiteScopeStoreWithStoreIdsEmpty(): array
    {
        $attribute = $this->getValidAttributeMock();
        $scope = $this->getMockBuilder(ScopeInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getIdentifier',
                'getValue',
                'getFallback',
            ])
            ->getMockForAbstractClass();
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStoreIds', 'getCode'])
            ->getMock();
        $website->expects($this->any())
            ->method('getStoreIds')
            ->willReturn([]);
        $website->expects($this->any())
            ->method('getCode')
            ->willReturn(Website::ADMIN_CODE);
        $scope->expects($this->any())
            ->method('getIdentifier')
            ->willReturn(Store::STORE_ID);
        $scope->expects($this->any())
            ->method('getValue')
            ->willReturn(1);
        $dbAdapater = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['quoteIdentifier'])
            ->getMock();
        $dbAdapater->expects($this->exactly(3))
            ->method('quoteIdentifier')
            ->willReturnCallback(
                function ($input) {
                    return sprintf('`%s`', $input);
                }
            );
        $metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getLinkField',
                'getEntityConnection',
            ])
            ->getMock();
        $metadata->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');
        $metadata->expects($this->any())
            ->method('getEntityConnection')
            ->willReturn($dbAdapater);
        $scopes = [$scope];

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getWebsite'])
            ->getMock();
        $store->expects($this->any())
            ->method('getWebsite')
            ->willReturn($website);

        $linkFieldValue = '5';
        $expectedConditions = [
            [
                'entity_id = ?' => $linkFieldValue,
                'attribute_id = ?' => 12,
                '`store_id` = ?' => Store::DEFAULT_STORE_ID,
            ]
        ];
        return [
            [
                $attribute,
                $metadata,
                $store,
                $scopes,
                $expectedConditions,
                $linkFieldValue
            ],
        ];
    }

    /**
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param StoreInterface $store
     * @param array $scopes
     * @param array $expectedConditions
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     * @dataProvider buildExistingAttributeWebsiteScopeSuccessDataProvider
     */
    public function testBuildExistingAttributeWebsiteScopeSuccess(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        StoreInterface $store,
        array $scopes,
        array $expectedConditions,
        string $linkFieldValue
    ) {
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn(
                $store
            );
        $result = $this->model->buildExistingAttributeWebsiteScope(
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
            ->onlyMethods([
                'quoteIdentifier',
            ])
            ->getMock();
        $dbAdapater->expects($this->exactly(3))
            ->method('quoteIdentifier')
            ->willReturnCallback(
                function ($input) {
                    return sprintf('`%s`', $input);
                }
            );

        $metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getLinkField',
                'getEntityConnection',
            ])
            ->getMock();
        $metadata->expects($this->any())
            ->method('getLinkField')
            ->willReturn(
                'entity_id'
            );
        $metadata->expects($this->exactly(3))
            ->method('getEntityConnection')
            ->willReturn(
                $dbAdapater
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

        $linkFieldValue = '5';

        return [
            [
                $attribute,
                $metadata,
                $store,
                $scopes,
                $expectedConditions,
                $linkFieldValue
            ],
        ];
    }

    /**
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param array $scopes
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     * @dataProvider buildNewAttributeWebsiteScopeUnappropriateAttributeDataProvider
     */
    public function testBuildNewAttributeWebsiteScopeUnappropriateAttribute(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        string $linkFieldValue
    ) {
        $this->storeManagerMock->expects($this->never())
            ->method('getStore');
        $result = $this->model->buildNewAttributesWebsiteScope(
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
            ->getMockForAbstractClass();

        $scopes = [];

        $linkFieldValue = '5';

        return [
            [
                $attribute,
                $metadata,
                $scopes,
                $linkFieldValue
            ],
        ];
    }

    /**
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param StoreInterface $store
     * @param array $scopes
     * @param array $expectedConditions
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     * @dataProvider buildNewAttributeWebsiteScopeSuccessDataProvider
     */
    public function testBuildNewAttributeWebsiteScopeSuccess(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        StoreInterface $store,
        array $scopes,
        array $expectedConditions,
        string $linkFieldValue
    ) {
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $result = $this->model->buildNewAttributesWebsiteScope(
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
            ->onlyMethods([
                'getLinkField',
            ])
            ->getMock();
        $metadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn('entity_id');

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

        $linkFieldValue = '5';

        return [
            [
                $attribute,
                $metadata,
                $store,
                $scopes,
                $expectedConditions,
                $linkFieldValue
            ],
        ];
    }

    /**
     * Test case for build new website attribute when website scope store with storeIds empty
     *
     * @param AbstractAttribute $attribute
     * @param EntityMetadataInterface $metadata
     * @param StoreInterface $store
     * @param array $scopes
     * @param array $expectedConditions
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     * @dataProvider buildNewAttributeWebsiteScopeStoreWithStoreIdsEmptyDataProvider
     */
    public function testBuildNewAttributeWebsiteScopeStoreWithStoreIdsEmpty(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        StoreInterface $store,
        array $scopes,
        array $expectedConditions,
        string $linkFieldValue
    ) {
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $result = $this->model->buildNewAttributesWebsiteScope(
            $attribute,
            $metadata,
            $scopes,
            $linkFieldValue
        );

        $this->assertEquals($expectedConditions, $result);
    }

    /**
     * Data provider for build new website attribute when website scope store with storeIds empty
     *
     * @return array
     */
    public function buildNewAttributeWebsiteScopeStoreWithStoreIdsEmptyDataProvider()
    {
        $attribute = $this->getValidAttributeMock();

        $metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLinkField'])
            ->getMock();
        $metadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn('entity_id');
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStoreIds', 'getCode'])
            ->getMock();
        $website->expects($this->any())
            ->method('getStoreIds')
            ->willReturn([]);
        $website->expects($this->any())
            ->method('getCode')
            ->willReturn(Website::ADMIN_CODE);
        $scopes = [
            $this->getValidScopeMock(),
        ];

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getWebsite',
            ])
            ->getMock();
        $store->expects($this->any())
            ->method('getWebsite')
            ->willReturn(
                $website
            );

        $linkFieldValue = '5';
        $expectedConditions = [
            [
                'entity_id' => $linkFieldValue,
                'attribute_id' => 12,
                'store_id' => Store::DEFAULT_STORE_ID,
            ]
        ];

        return [
            [
                $attribute,
                $metadata,
                $store,
                $scopes,
                $expectedConditions,
                $linkFieldValue
            ],
        ];
    }

    /**
     * @return MockObject
     */
    private function getValidAttributeMock()
    {
        $attribute = $this->getMockBuilder(CatalogEavAttribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'isScopeWebsite',
                'getAttributeId',
            ])
            ->getMock();
        $attribute->expects($this->never())
            ->method('isScopeWebsite')
            ->willReturn(
                true
            );
        $attribute->expects($this->once())
            ->method('getAttributeId')
            ->willReturn(
                12
            );

        return $attribute;
    }

    /**
     * @return MockObject
     */
    private function getValidStoreMock()
    {
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getStoreIds',
            ])
            ->getMock();
        $website->expects($this->any())
            ->method('getStoreIds')
            ->willReturn(
                [
                    1,
                    2,
                    3,
                ]
            );

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getWebsite',
            ])
            ->getMock();
        $store->expects($this->any())
            ->method('getWebsite')
            ->willReturn(
                $website
            );

        return $store;
    }

    /**
     * @return MockObject
     */
    private function getValidScopeMock()
    {
        $scope = $this->getMockBuilder(ScopeInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getIdentifier',
                'getValue',
                'getFallback',
            ])
            ->getMockForAbstractClass();
        $scope->expects($this->any())
            ->method('getIdentifier')
            ->willReturn(
                Store::STORE_ID
            );
        $scope->expects($this->any())
            ->method('getValue')
            ->willReturn(1);

        return $scope;
    }
}
