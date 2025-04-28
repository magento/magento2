<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
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
        self::$storeManagerMock = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore'])
            ->getMock();
        self::$model = new ConditionBuilder(self::$storeManagerMock);
    }

    /**
     * @param \Closure $attribute
     * @param \Closure $metadata
     * @param array $scopes
     * @param string $linkFieldValue
     * @throws NoSuchEntityException
     * @dataProvider buildExistingAttributeWebsiteScopeInappropriateAttributeDataProvider
     */
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
     * @dataProvider buildExistingAttributeWebsiteScopeStoreScopeNotFoundDataProvider
     */
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
     * @dataProvider buildExistingAttributeWebsiteScopeStoreWebsiteNotFoundDataProvider
     */
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
     * @dataProvider buildExistingAttributeWebsiteScopeStoreWithStoreIdsEmpty
     */
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
        self::$storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
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
     * @dataProvider buildExistingAttributeWebsiteScopeSuccessDataProvider
     */
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
        self::$storeManagerMock->expects($this->any())
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
     * @dataProvider buildNewAttributeWebsiteScopeUnappropriateAttributeDataProvider
     */
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
     * @dataProvider buildNewAttributeWebsiteScopeSuccessDataProvider
     */
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
        self::$storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
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
     * @dataProvider buildNewAttributeWebsiteScopeStoreWithStoreIdsEmptyDataProvider
     */
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
        self::$storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
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
        $attribute->expects($this->any())
            ->method('getAttributeId')
            ->willReturn(
                12
            );

        return $attribute;
    }

    /**
     * @return MockObject
     */
    protected function getValidStoreMock()
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
    protected function getValidScopeMock()
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

    protected function getMockForAttributeClass($atr)
    {
        $attribute = "";
        if ($atr == "Attribute") {
            $attribute = $this->getMockBuilder(Attribute::class)
                ->disableOriginalConstructor()
                ->getMock();
        } elseif ($atr == "CatalogEavAttribute") {
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
        }

        return $attribute;
    }

    protected function getMockForMetadataClass($return)
    {
        if ($return == null) {
            return $this->getMockBuilder(EntityMetadataInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
        } elseif ($return == 'entity_id') {
            $metadata = $this->getMockBuilder(EntityMetadata::class)
                ->disableOriginalConstructor()
                ->onlyMethods([
                    'getLinkField',
                ])
                ->getMock();
            $metadata->expects($this->once())
                ->method('getLinkField')
                ->willReturn('entity_id');
            return $metadata;
        } else {
            $dbAdapater = $this->getMockBuilder(Mysql::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['quoteIdentifier'])
                ->getMock();
            $dbAdapater->expects($this->any())
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
            return $metadata;
        }
    }

    protected function getMockForScopeClass()
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
            ->willReturn(
                1
            );
        $scopes = [
            $scope,
        ];

        return $scopes;
    }

    protected function getMockForStoreClass($return)
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getWebsite',
            ])
            ->getMock();
        if (!$return) {
            $store->expects($this->any())
                ->method('getWebsite')
                ->willReturn(
                    false
                );
        } else {
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
            $store->expects($this->any())
                ->method('getWebsite')
                ->willReturn(
                    $website
                );
        }
        return $store;
    }
}
