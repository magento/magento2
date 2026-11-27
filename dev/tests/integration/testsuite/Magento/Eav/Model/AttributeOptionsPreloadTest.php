<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Model;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeManagementInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Test\Fixture\AttributeSet as AttributeSetFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\DB\Profiler;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Verify REST service results batch options without contaminating EAV's shared models.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AppArea(Area::AREA_WEBAPI_REST)]
#[AppIsolation(true)]
#[DbIsolation(true)]
class AttributeOptionsPreloadTest extends TestCase
{
    /**
     * Attribute list route service identifiers.
     *
     * @var array<string, array>
     */
    private const ROUTES = [
        'set' => [ProductAttributeManagementInterface::class, 'getAttributes'],
        'list' => [ProductAttributeRepositoryInterface::class, 'getList'],
    ];

    /**
     * Compare two and twelve fixture selects in both service paths and across store switches.
     *
     * @param string $service
     * @return void
     */
    #[DataFixture(AttributeSetFixture::class, as: 'small_set')]
    #[DataFixture(AttributeSetFixture::class, as: 'large_set')]
    #[DataFixture(AttributeFixture::class, [
        'frontend_input' => 'select',
        'backend_type' => 'int',
        'options' => [
            ['label' => 'Zulu', 'sort_order' => 10],
            ['label' => 'Beta', 'sort_order' => 2],
            [
                'label' => 'Alpha',
                'sort_order' => 2,
                'is_default' => true,
                'store_labels' => [['store_id' => 1, 'label' => 'Localized Alpha']],
            ],
        ],
    ], as: 'attribute', count: 12)]
    #[DataProvider('serviceProvider')]
    public function testOptionsAreBatchedOnCopiesAndSharedAttributesCanBeSaved(string $service): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(State::class)->setAreaCode(Area::AREA_WEBAPI_REST);
        $this->assignAttributesToSet('small_set', 2);
        $this->assignAttributesToSet('large_set', 12);
        $objectManager->get(Config::class)->clear();
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $originalStore = $storeManager->getStore();
        try {
            foreach ([0, 1, 0] as $storeId) {
                $storeManager->setCurrentStore($storeId);
                $small = $this->measure($service, 2);
                $large = $this->measure($service, 12);
                $this->assertSame(1, $small['queries']);
                $this->assertSame($small['queries'], $large['queries']);
                $this->assertFixtureCount($this->attributeItems($small['data']), 2);
                $this->assertFixtureCount($this->attributeItems($large['data']), 12);
                $this->assertOutputUnchanged($small, $service);
                $this->assertOutputUnchanged($large, $service);
                $this->assertStoreLabels($large['output'], $service, $storeId);
                $this->assertSharedSavePreservesOptions();
            }
        } finally {
            $storeManager->setCurrentStore($originalStore);
        }
    }

    /**
     * Keep product saves free of option loading when their internal attribute lists are read.
     *
     * @return void
     */
    #[DataFixture(ProductFixture::class, as: 'product')]
    #[DataFixture(AttributeFixture::class, [
        'frontend_input' => 'select',
        'backend_type' => 'int',
        'options' => [['label' => 'Option']],
    ], count: 12)]
    public function testProductSaveDoesNotPreloadAttributeOptions(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(State::class)->setAreaCode(Area::AREA_WEBAPI_REST);
        $repository = $objectManager->get(ProductRepositoryInterface::class);
        $product = $repository->get(DataFixtureStorageManager::getStorage()->get('product')->getSku());
        $objectManager->get(Config::class)->clear();
        $measurement = $this->profile(static fn () => $repository->save($product));
        $this->assertSame(0, $measurement['queries']);
    }

    /**
     * Supply both Catalog service entry points.
     *
     * @return array
     */
    public static function serviceProvider(): array
    {
        return ['attribute set' => ['set'], 'attribute list' => ['list']];
    }

    /**
     * Assign fixture attributes using a group belonging to the target set.
     *
     * @param string $setFixture
     * @param int $count
     * @return void
     */
    private function assignAttributesToSet(string $setFixture, int $count): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $setId = (int)$fixtures->get($setFixture)->getAttributeSetId();
        $groupId = $objectManager->get(EavSetup::class)->getDefaultAttributeGroupId(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $setId
        );
        $management = $objectManager->get(ProductAttributeManagementInterface::class);
        foreach ($this->fixtureCodes($count) as $code) {
            $management->assign($setId, $groupId, $code, 0);
        }
    }

    /**
     * Count option SELECTs across service execution and option serialization reads.
     *
     * @param string $service
     * @param int $count
     * @return array
     */
    private function measure(string $service, int $count): array
    {
        [$serviceClass, $method] = self::ROUTES[$service];
        $measurement = $this->profile(function () use ($service, $count, $serviceClass, $method): array {
            $data = $this->loadAttributes($service, $count);
            $output = Bootstrap::getObjectManager()->get(ServiceOutputProcessor::class)
                ->process($data, $serviceClass, $method);
            return ['data' => $data, 'output' => $output];
        });
        return $measurement['result'] + ['queries' => $measurement['queries']];
    }

    /**
     * Count option SELECTs during one operation and restore the connection profiler.
     *
     * @param callable $operation
     * @return array
     */
    private function profile(callable $operation): array
    {
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $originalProfiler = $connection->getProfiler();
        $profiler = new Profiler(true);
        $connection->setProfiler($profiler);
        try {
            $result = $operation();
            $queries = array_filter(
                $profiler->getQueryProfiles(Profiler::SELECT) ?: [],
                static fn ($query): bool => str_contains(
                    $query->getQuery(),
                    'FROM `' . $resource->getTableName('eav_attribute_option') . '`'
                )
            );
            return ['result' => $result, 'queries' => count($queries)];
        } finally {
            $connection->setProfiler($originalProfiler);
        }
    }

    /**
     * Load the raw route result without preparing options on internal service calls.
     *
     * @param string $service
     * @param int $count
     * @return array|AttributeSearchResultsInterface
     */
    private function loadAttributes(string $service, int $count): array|AttributeSearchResultsInterface
    {
        $objectManager = Bootstrap::getObjectManager();
        if ($service === 'set') {
            $set = DataFixtureStorageManager::getStorage()->get($count === 2 ? 'small_set' : 'large_set');
            return $objectManager->get(ProductAttributeManagementInterface::class)->getAttributes(
                $set->getAttributeSetId()
            );
        }
        $criteria = $objectManager->create(SearchCriteriaBuilder::class)
            ->addFilter('attribute_code', $this->fixtureCodes($count), 'in')->create();
        return $objectManager->get(ProductAttributeRepositoryInterface::class)->getList($criteria);
    }

    /**
     * Resolve the requested fixture attribute codes in creation order.
     *
     * @param int $count
     * @return string[]
     */
    private function fixtureCodes(int $count): array
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $codes = [];
        for ($index = 1; $index <= $count; $index++) {
            $codes[] = $fixtures->get('attribute' . $index)->getAttributeCode();
        }
        return $codes;
    }

    /**
     * Ensure the requests actually return the requested number of fixture selects.
     *
     * @param AbstractAttribute[] $items
     * @param int $count
     * @return void
     */
    private function assertFixtureCount(array $items, int $count): void
    {
        $codes = array_map(static fn ($attribute) => $attribute->getAttributeCode(), $items);
        $this->assertCount($count, array_intersect($codes, $this->fixtureCodes(12)));
    }

    /**
     * Compare the full REST output against the upstream processor without interception.
     *
     * @param array $measurement
     * @param string $service
     * @return void
     */
    private function assertOutputUnchanged(array $measurement, string $service): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $processor = new ServiceOutputProcessor(
            $objectManager->get(DataObjectProcessor::class),
            $objectManager->get(MethodsMap::class),
            $objectManager->get(TypeProcessor::class)
        );
        [$serviceClass, $method] = self::ROUTES[$service];
        $this->assertSame(
            json_encode($processor->process($measurement['data'], $serviceClass, $method), JSON_THROW_ON_ERROR),
            json_encode($measurement['output'], JSON_THROW_ON_ERROR)
        );
        $sharedConfig = $objectManager->get(Config::class);
        foreach ($this->attributeItems($measurement['data']) as $attribute) {
            $this->assertFalse($attribute->hasData('options'), $attribute->getAttributeCode());
            $shared = $sharedConfig->getAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attribute->getAttributeCode()
            );
            $this->assertFalse($shared->hasData('options'), $attribute->getAttributeCode());
        }
    }

    /**
     * Read models from either raw route result shape.
     *
     * @param array|AttributeSearchResultsInterface $data
     * @return AbstractAttribute[]
     */
    private function attributeItems(array|AttributeSearchResultsInterface $data): array
    {
        return is_array($data) ? $data : $data->getItems();
    }

    /**
     * Verify translated labels, fallback labels, tied positions and the blank option.
     *
     * @param array $output
     * @param string $service
     * @param int $storeId
     * @return void
     */
    private function assertStoreLabels(array $output, string $service, int $storeId): void
    {
        $code = $this->fixtureCodes(1)[0];
        $items = $service === 'list' ? $output['items'] : $output;
        $byCode = array_column($items, null, 'attribute_code');
        $options = $byCode[$code]['options'];
        $this->assertSame('', $options[0]['value']);
        $this->assertSame(
            $storeId === 1 ? [' ', 'Beta', 'Localized Alpha', 'Zulu'] : [' ', 'Alpha', 'Beta', 'Zulu'],
            array_column($options, 'label')
        );
    }

    /**
     * Save the shared repository model after listing and compare every persisted option field.
     *
     * @return void
     */
    private function assertSharedSavePreservesOptions(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $repository = $objectManager->get(ProductAttributeRepositoryInterface::class);
        $criteria = $objectManager->create(SearchCriteriaBuilder::class)
            ->addFilter('attribute_code', $this->fixtureCodes(1)[0])->create();
        $results = $repository->getList($criteria);
        $objectManager->get(ServiceOutputProcessor::class)->process(
            $results,
            ProductAttributeRepositoryInterface::class,
            'getList'
        );
        $returned = $results->getItems()[0];
        $shared = $repository->get($returned->getAttributeCode());
        $this->assertSame($objectManager->get(Config::class)->getAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $returned->getAttributeCode()
        ), $shared);
        $this->assertSame($returned, $shared);
        $this->assertFalse($shared->hasData('options'));
        $before = $this->optionSnapshot((int)$shared->getAttributeId());
        $repository->save($shared);
        $this->assertSame($before, $this->optionSnapshot((int)$shared->getAttributeId()));
    }

    /**
     * Capture option IDs, positions, all store labels and the attribute default.
     *
     * @param int $attributeId
     * @return array
     */
    private function optionSnapshot(int $attributeId): array
    {
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $options = $connection->select()->from(
            ['options' => $resource->getTableName('eav_attribute_option')]
        )->join(
            ['labels' => $resource->getTableName('eav_attribute_option_value')],
            'labels.option_id = options.option_id'
        )->where('options.attribute_id = ?', $attributeId)->order(['options.option_id', 'labels.store_id']);
        $default = $connection->select()->from(
            $resource->getTableName('eav_attribute'),
            ['default_value']
        )->where('attribute_id = ?', $attributeId);
        return ['options' => $connection->fetchAll($options), 'default' => $connection->fetchOne($default)];
    }
}
