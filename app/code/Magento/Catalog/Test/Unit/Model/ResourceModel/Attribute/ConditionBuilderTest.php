<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Catalog\Model\ResourceModel\Attribute\AttributeConditionsBuilder
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
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
    private static $storeManagerMock;

    /**
     * @var ConditionBuilder
     */
    private static $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        self::$storeManagerMock = $this->createPartialMock(StoreManager::class, ['getStore']);
        self::$model = new ConditionBuilder(self::$storeManagerMock);
    }

    /**
     * @param \Closure $attribute
     * @param \Closure $metadata
     * @param array $scopes
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     */
    #[DataProvider('buildExistingAttributeWebsiteScopeInappropriateAttributeDataProvider')]
    public function testBuildExistingAttributeWebsiteScopeInappropriateAttribute(
        \Closure $attribute,
        \Closure $metadata,
        array $scopes,
        string $linkFieldValue
    ) {
        $attribute = $attribute($this);
        $metadata = $metadata($this);
        self::$storeManagerMock->expects($this->never())
            ->method('getStore');
        $result = self::$model->buildExistingAttributeWebsiteScope(
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
    public static function buildExistingAttributeWebsiteScopeInappropriateAttributeDataProvider()
    {

        $attribute = static fn (self $testCase) => $testCase->getMockForAttributeClass('Attribute');

        $metadata = static fn (self $testCase) => $testCase->getMockForMetadataClass(null);

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
     * @param \Closure $attribute
     * @param \Closure $metadata
     * @param array $scopes
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     */
    #[DataProvider('buildExistingAttributeWebsiteScopeStoreScopeNotFoundDataProvider')]
    public function testBuildExistingAttributeWebsiteScopeStoreScopeNotFound(
        \Closure $attribute,
        \Closure $metadata,
        array $scopes,
        string $linkFieldValue
    ) {
        $attribute = $attribute($this);
        $metadata = $metadata($this);
        self::$storeManagerMock->expects($this->any())
            ->method('getStore');
        $result = self::$model->buildExistingAttributeWebsiteScope(
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
    public static function buildExistingAttributeWebsiteScopeStoreScopeNotFoundDataProvider()
    {
        $attribute = static fn (self $testCase) => $testCase->getMockForAttributeClass('CatalogEavAttribute');

        $metadata = static fn (self $testCase) => $testCase->getMockForMetadataClass(null);

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
     * @param \Closure $attribute
     * @param \Closure $metadata
     * @param \Closure $store
     * @param array $scopes
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     */
    #[DataProvider('buildExistingAttributeWebsiteScopeStoreWebsiteNotFoundDataProvider')]
    public function testBuildExistingAttributeWebsiteScopeStoreWebsiteNotFound(
        \Closure $attribute,
        \Closure $metadata,
        \Closure $store,
        \Closure $scopes,
        string $linkFieldValue
    ) {
        $attribute = $attribute($this);
        $metadata = $metadata($this);
        $store = $store($this);
        $scopes = $scopes($this);
        self::$storeManagerMock->expects(self::any())
            ->method('getStore')
            ->willReturn(
                $store
            );
        $result = self::$model->buildExistingAttributeWebsiteScope(
            $attribute,
            $metadata,
            $scopes,
            $linkFieldValue
        );

        self::assertEquals([], $result);
    }

    /**
     * @return array
     */
    public static function buildExistingAttributeWebsiteScopeStoreWebsiteNotFoundDataProvider()
    {
        $attribute = static fn (self $testCase) => $testCase->getMockForAttributeClass('CatalogEavAttribute');

        $metadata = static fn (self $testCase) => $testCase->getMockForMetadataClass(null);

        $scopes = static fn (self $testCase) => $testCase->getMockForScopeClass();

        $store = static fn (self $testCase) => $testCase->getMockForStoreClass(false);

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
     * @param \Closure $attribute
     * @param \Closure $metadata
     * @param \Closure $store
     * @param \Closure $scopes
     * @param array $expectedConditions
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     */
    #[DataProvider('buildExistingAttributeWebsiteScopeStoreWithStoreIdsEmpty')]
    public function testBuildExistingAttributeWebsiteScopeStoreWithStoreIdsEmpty(
        \Closure $attribute,
        \Closure $metadata,
        \Closure $store,
        \Closure $scopes,
        array $expectedConditions,
        string $linkFieldValue
    ) {
        $attribute = $attribute($this);
        $metadata = $metadata($this);
        $store = $store($this);
        $scopes = $scopes($this);
        self::$storeManagerMock->method('getStore')->willReturn($store);
        $result = self::$model->buildExistingAttributeWebsiteScope(
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
    public static function buildExistingAttributeWebsiteScopeStoreWithStoreIdsEmpty(): array
    {
        $attribute = static fn (self $testCase) => $testCase->getValidAttributeMock();

        $store = static fn (self $testCase) => $testCase->getMockForStoreClass('website');

        $metadata = static fn (self $testCase) => $testCase->getMockForMetadataClass('dbAdapter');

        $scopes = static fn (self $testCase) => $testCase->getMockForScopeClass();

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
     * @param \Closure $attribute
     * @param \Closure $metadata
     * @param \Closure $store
     * @param array $scopes
     * @param array $expectedConditions
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     */
    #[DataProvider('buildExistingAttributeWebsiteScopeSuccessDataProvider')]
    public function testBuildExistingAttributeWebsiteScopeSuccess(
        \Closure $attribute,
        \Closure $metadata,
        \Closure $store,
        array $scopes,
        array $expectedConditions,
        string $linkFieldValue
    ) {
        $attribute = $attribute($this);
        $metadata = $metadata($this);
        $store = $store($this);
        $scopes[0] = $scopes[0]($this);
        self::$storeManagerMock->method('getStore')->willReturn(
            $store
        );
        $result = self::$model->buildExistingAttributeWebsiteScope(
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
    public static function buildExistingAttributeWebsiteScopeSuccessDataProvider()
    {
        $attribute = static fn (self $testCase) => $testCase->getValidAttributeMock();

        $metadata = static fn (self $testCase) => $testCase->getMockForMetadataClass('dbAdapter');

        $scopes = [
            static fn (self $testCase) => $testCase->getValidScopeMock(),
        ];

        $store = static fn (self $testCase) => $testCase->getValidStoreMock();

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
     * @param \Closure $attribute
     * @param \Closure $metadata
     * @param array $scopes
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     */
    #[DataProvider('buildNewAttributeWebsiteScopeUnappropriateAttributeDataProvider')]
    public function testBuildNewAttributeWebsiteScopeUnappropriateAttribute(
        \Closure $attribute,
        \Closure $metadata,
        array $scopes,
        string $linkFieldValue
    ) {
        $attribute = $attribute($this);
        $metadata = $metadata($this);
        self::$storeManagerMock->expects($this->never())
            ->method('getStore');
        $result = self::$model->buildNewAttributesWebsiteScope(
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
    public static function buildNewAttributeWebsiteScopeUnappropriateAttributeDataProvider()
    {
        $attribute = static fn (self $testCase) => $testCase->getValidAttributeMock();

        $metadata = static fn (self $testCase) => $testCase->getMockForMetadataClass(null);

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
     * @param \Closure $attribute
     * @param \Closure $metadata
     * @param \Closure $store
     * @param array $scopes
     * @param array $expectedConditions
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     */
    #[DataProvider('buildNewAttributeWebsiteScopeSuccessDataProvider')]
    public function testBuildNewAttributeWebsiteScopeSuccess(
        \Closure $attribute,
        \Closure $metadata,
        \Closure $store,
        array $scopes,
        array $expectedConditions,
        string $linkFieldValue
    ) {
        $attribute = $attribute($this);
        $metadata = $metadata($this);
        $store = $store($this);
        $scopes[0] = $scopes[0]($this);
        self::$storeManagerMock->method('getStore')->willReturn($store);
        $result = self::$model->buildNewAttributesWebsiteScope(
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
    public static function buildNewAttributeWebsiteScopeSuccessDataProvider()
    {
        $attribute = static fn (self $testCase) => $testCase->getValidAttributeMock();

        $metadata = static fn (self $testCase) => $testCase->getMockForMetadataClass("entity_id");

        $scopes = [
            static fn (self $testCase) => $testCase->getValidScopeMock(),
        ];

        $store = static fn (self $testCase) => $testCase->getValidStoreMock();

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
     * @param \Closure $attribute
     * @param \Closure $metadata
     * @param \Closure $store
     * @param array $scopes
     * @param array $expectedConditions
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     */
    #[DataProvider('buildNewAttributeWebsiteScopeStoreWithStoreIdsEmptyDataProvider')]
    public function testBuildNewAttributeWebsiteScopeStoreWithStoreIdsEmpty(
        \Closure $attribute,
        \Closure $metadata,
        \Closure $store,
        array $scopes,
        array $expectedConditions,
        string $linkFieldValue
    ) {
        $attribute = $attribute($this);
        $metadata = $metadata($this);
        $store = $store($this);
        $scopes[0] = $scopes[0]($this);
        self::$storeManagerMock->method('getStore')->willReturn($store);
        $result = self::$model->buildNewAttributesWebsiteScope(
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
    public static function buildNewAttributeWebsiteScopeStoreWithStoreIdsEmptyDataProvider()
    {
        $attribute = static fn (self $testCase) => $testCase->getValidAttributeMock();

        $metadata = static fn (self $testCase) => $testCase->getMockForMetadataClass("entity_id");

        $store = static fn (self $testCase) => $testCase->getMockForStoreClass('website');

        $scopes = [
            static fn (self $testCase) => $testCase->getValidScopeMock(),
        ];

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
    protected function getValidAttributeMock()
    {
        $attribute = $this->createPartialMock(CatalogEavAttribute::class, [
            'isScopeWebsite',
            'getAttributeId',
        ]);
        $attribute->expects($this->never())
            ->method('isScopeWebsite')
            ->willReturn(
                true
            );
        $attribute->method('getAttributeId')->willReturn(
            12
        );

        return $attribute;
    }

    /**
     * @return MockObject
     */
    protected function getValidStoreMock()
    {
        $website = $this->createPartialMock(Website::class, ['getStoreIds']);
        $website->method('getStoreIds')->willReturn(
            [
                    1,
                    2,
                    3,
                ]
        );

        $store = $this->createPartialMock(Store::class, ['getWebsite']);
        $store->method('getWebsite')->willReturn(
            $website
        );

        return $store;
    }

    /**
     * @return MockObject
     */
    protected function getValidScopeMock()
    {
        $scope = $this->createMock(ScopeInterface::class);
        $scope->method('getIdentifier')->willReturn(
            Store::STORE_ID
        );
        $scope->method('getValue')->willReturn(1);

        return $scope;
    }

    protected function getMockForAttributeClass($atr)
    {
        $attribute = "";
        if ($atr == "Attribute") {
            $attribute = $this->createMock(Attribute::class);
        } elseif ($atr == "CatalogEavAttribute") {
            $attribute = $this->createPartialMock(CatalogEavAttribute::class, ['isScopeWebsite']);

            $attribute->expects($this->never())
                ->method('isScopeWebsite')
                ->willReturn(
                    true
                );
        }

        return $attribute;
    }

    protected function getMockForMetadataClass($return)
    {
        if ($return == null) {
            return $this->createMock(EntityMetadataInterface::class);
        } elseif ($return == 'entity_id') {
            $metadata = $this->createPartialMock(EntityMetadata::class, ['getLinkField']);
            $metadata->expects($this->once())
                ->method('getLinkField')
                ->willReturn('entity_id');
            return $metadata;
        } else {
            $dbAdapater = $this->createPartialMock(Mysql::class, ['quoteIdentifier']);
            $dbAdapater->expects($this->any())
                ->method('quoteIdentifier')
                ->willReturnCallback(
                    function ($input) {
                        return sprintf('`%s`', $input);
                    }
                );
            $metadata = $this->createPartialMock(EntityMetadata::class, [
                'getLinkField',
                'getEntityConnection',
            ]);
            $metadata->method('getLinkField')->willReturn('entity_id');
            $metadata->method('getEntityConnection')->willReturn($dbAdapater);
            return $metadata;
        }
    }

    protected function getMockForScopeClass()
    {
        $scope = $this->createMock(ScopeInterface::class);
        $scope->method('getIdentifier')->willReturn(
            Store::STORE_ID
        );
        $scope->method('getValue')->willReturn(
            1
        );
        $scopes = [
            $scope,
        ];

        return $scopes;
    }

    protected function getMockForStoreClass($return)
    {
        $store = $this->createPartialMock(Store::class, ['getWebsite']);
        if (!$return) {
            $store->method('getWebsite')->willReturn(
                false
            );
        } else {
            $website = $this->createPartialMock(Website::class, ['getStoreIds', 'getCode']);
            $website->method('getStoreIds')->willReturn([]);
            $website->method('getCode')->willReturn(Website::ADMIN_CODE);
            $store->method('getWebsite')->willReturn(
                $website
            );
        }
        return $store;
    }
}
