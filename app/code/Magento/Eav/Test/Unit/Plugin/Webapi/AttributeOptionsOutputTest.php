<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Plugin\Webapi;

use ArrayIterator;
use Magento\Catalog\Api\ProductAttributeManagementInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Model\AttributeSearchResults;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\OptionsPreloader;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Eav\Plugin\Webapi\AttributeOptionsOutput;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\DataObject;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Verify Web API option preparation leaves shared models unchanged.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeOptionsOutputTest extends TestCase
{
    /**
     * Option collection factory.
     *
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * Current store resolver.
     *
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * Output options preloader.
     *
     * @var OptionsPreloader
     */
    private OptionsPreloader $preloader;

    /**
     * Web API output plugin.
     *
     * @var AttributeOptionsOutput
     */
    private AttributeOptionsOutput $plugin;

    /**
     * Build the plugin with isolated collection and store dependencies.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->preloader = new OptionsPreloader($this->collectionFactory, $this->storeManager);
        $this->plugin = new AttributeOptionsOutput($this->preloader);
    }

    /**
     * Preserve keys, order and value types while cloning only eligible attributes.
     *
     * @return void
     */
    public function testAttributeSetResultsAreCopiesWithOneOptionCollection(): void
    {
        $first = $this->attribute(10, 2);
        $second = $this->attribute(11, 2);
        $empty = $this->attribute(12, 2);
        $prepared = $this->attribute(13, 2);
        $prepared->setData('options', [['label' => 'Prepared', 'value' => '1']]);
        $originalData = $first->getData();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn(
            $this->collection(2, [10, 11, 12], [
                new DataObject(['attribute_id' => '11', 'option_id' => '21', 'value' => 'First']),
                new DataObject(['attribute_id' => '10', 'option_id' => '22', 'value' => 'Second']),
                new DataObject(['attribute_id' => '10', 'option_id' => '23', 'value' => 'Third']),
            ])
        );
        $result = $this->prepareAttributes([9 => $first, 'prepared' => $prepared, 3 => $second, 'empty' => $empty]);

        $this->assertSame([9, 'prepared', 3, 'empty'], array_keys($result));
        $this->assertNotSame($first, $result[9]);
        $this->assertNotSame($second, $result[3]);
        $this->assertSame($prepared, $result['prepared']);
        $this->assertSame($originalData, $first->getData());
        $this->assertFalse($second->hasData('options'));
        $blank = ['label' => ' ', 'value' => ''];
        $this->assertSame([
            $blank,
            ['value' => '22', 'label' => 'Second'],
            ['value' => '23', 'label' => 'Third'],
        ], $result[9]->getData('options'));
        $this->assertSame([$blank, ['value' => '21', 'label' => 'First']], $result[3]->getData('options'));
        $this->assertSame([$blank], $result['empty']->getData('options'));
        $this->assertFalse($empty->hasData('options'));
    }

    /**
     * Replace search items with clones without changing search result metadata.
     *
     * @return void
     */
    public function testSearchResultsContainCopies(): void
    {
        $attribute = $this->attribute(10, 0);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn(
            $this->collection(0, [10], [])
        );
        $criteria = new SearchCriteria();
        $searchResults = new AttributeSearchResults();
        $searchResults->setItems(['attribute' => $attribute])->setTotalCount(1)->setSearchCriteria($criteria);
        $arguments = $this->plugin->beforeProcess(
            $this->createMock(ServiceOutputProcessor::class),
            $searchResults,
            ProductAttributeRepositoryInterface::class,
            'getList'
        );
        $result = $arguments[0];

        $this->assertNotSame($searchResults, $result);
        $this->assertSame(['attribute' => $attribute], $searchResults->getItems());
        $this->assertSame(['attribute'], array_keys($result->getItems()));
        $this->assertNotSame($attribute, $result->getItems()['attribute']);
        $this->assertSame([['label' => ' ', 'value' => '']], $result->getItems()['attribute']->getData('options'));
        $this->assertSame($criteria, $result->getSearchCriteria());
        $this->assertSame(1, $result->getTotalCount());
        $this->assertSame([ProductAttributeRepositoryInterface::class, 'getList'], array_slice($arguments, 1));
        $this->assertFalse($attribute->hasData('options'));
    }

    /**
     * Leave internal service results and unrelated route output untouched.
     *
     * @param string $service
     * @param string $method
     * @return void
     */
    #[DataProvider('unrelatedServices')]
    public function testUnrelatedOutputIsUnchanged(string $service, string $method): void
    {
        $attribute = $this->attribute(10, 0);
        $this->collectionFactory->expects($this->never())->method('create');
        $data = ['original' => $attribute];
        $this->assertSame([$data, $service, $method], $this->plugin->beforeProcess(
            $this->createMock(ServiceOutputProcessor::class),
            $data,
            $service,
            $method
        ));
    }

    /**
     * Supply wrong methods and unexposed service interfaces.
     *
     * @return array
     */
    public static function unrelatedServices(): array
    {
        return [
            'wrong method' => [ProductAttributeRepositoryInterface::class, 'get'],
            'wrong service' => [ProductAttributeManagementInterface::class, 'getList'],
            'unexposed EAV service' => [AttributeManagementInterface::class, 'getAttributes'],
        ];
    }

    /**
     * Skip unavailable sources, overridden sources and attributes without a source.
     *
     * @return void
     */
    public function testIneligibleAndOrphanedSourcesAreUnchanged(): void
    {
        $noSource = $this->createPartialMock(Attribute::class, ['usesSource', 'getSource']);
        $noSource->method('usesSource')->willReturn(false);
        $noSource->expects($this->never())->method('getSource');
        $orphaned = $this->createPartialMock(Attribute::class, ['usesSource', 'getSource']);
        $orphaned->method('usesSource')->willReturn(true);
        $orphaned->method('getSource')->willThrowException(new RuntimeException('Unavailable source'));
        $overridden = $this->createMock(Table::class);
        $overridden->expects($this->never())->method('getAllOptions');
        $boolean = $this->createMock(Boolean::class);
        $boolean->expects($this->never())->method('getAllOptions');
        $items = [
            'no_source' => $noSource,
            'orphaned' => $orphaned,
            'overridden' => $this->attribute(11, 0, $overridden),
            'boolean' => $this->attribute(12, 0, $boolean),
        ];
        $this->collectionFactory->expects($this->never())->method('create');
        $this->storeManager->expects($this->never())->method('getStore');
        $this->assertSame($items, $this->prepareAttributes($items));
    }

    /**
     * Resolve the query identity from the source's attribute rather than the result model.
     *
     * @return void
     */
    public function testSourceAttributeControlsStoreAndAttributeFilter(): void
    {
        $source = $this->tableSource();
        $sourceAttribute = $this->attribute(10, 2);
        $attribute = $this->attribute(99, 0, $source);
        $source->setAttribute($sourceAttribute);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn(
            $this->collection(2, [10], [new DataObject([
                'attribute_id' => '10', 'option_id' => '20', 'value' => 'Source label',
            ])])
        );
        $this->storeManager->expects($this->never())->method('getStore');

        $result = $this->preloader->preload([$attribute]);

        $this->assertSame('Source label', $result[0]->getData('options')[1]['label']);
        $this->assertFalse($attribute->hasData('options'));
    }

    /**
     * Keep repeated calls independent across stores, including the explicit admin store.
     *
     * @return void
     */
    public function testRepeatedCallsResolveTheStoreAgain(): void
    {
        $attribute = $this->attribute(10, null);
        $store = $this->createMock(Store::class);
        $store->method('getId')->willReturn(2);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $this->collectionFactory->expects($this->exactly(2))->method('create')->willReturnOnConsecutiveCalls(
            $this->collection(2, [10], []),
            $this->collection(0, [10], [])
        );
        $first = $this->preloader->preload([$attribute]);
        $attribute->setData('store_id', 0);
        $second = $this->preloader->preload([$attribute]);

        $this->assertNotSame($first[0], $second[0]);
        $this->assertFalse($attribute->hasData('options'));
    }

    /**
     * Preload, reset, then handle a different source class independently.
     *
     * @return void
     */
    public function testInheritedSourceIsEligibleAfterReset(): void
    {
        $source = new class (
            $this->collectionFactory,
            $this->createMock(OptionFactory::class),
            $this->storeManager
        ) extends Table {
        };
        $attribute = $this->attribute(10, 0, $source);
        $first = $this->attribute(11, 0);
        $this->collectionFactory->expects($this->exactly(2))->method('create')->willReturnOnConsecutiveCalls(
            $this->collection(0, [11], []),
            $this->collection(0, [10], [])
        );
        $this->assertNotSame($first, $this->preloader->preload([$first])[0]);
        $this->preloader->_resetState();
        $result = $this->preloader->preload([$attribute]);

        $this->assertNotSame($attribute, $result[0]);
        $this->assertFalse($attribute->hasData('options'));
    }

    /**
     * Prepare array output while preserving the remaining process arguments.
     *
     * @param array $attributes
     * @return array
     */
    private function prepareAttributes(array $attributes): array
    {
        $arguments = $this->plugin->beforeProcess(
            $this->createMock(ServiceOutputProcessor::class),
            $attributes,
            ProductAttributeManagementInterface::class,
            'getAttributes'
        );
        $this->assertSame([ProductAttributeManagementInterface::class, 'getAttributes'], array_slice($arguments, 1));
        return $arguments[0];
    }

    /**
     * Construct a source with the unchanged table implementation.
     *
     * @return Table
     */
    private function tableSource(): Table
    {
        return new Table($this->collectionFactory, $this->createMock(OptionFactory::class), $this->storeManager);
    }

    /**
     * Build a source-bearing attribute without database dependencies.
     *
     * @param int $id
     * @param int|null $storeId
     * @param AbstractSource|null $source
     * @return Attribute
     */
    private function attribute(int $id, ?int $storeId, ?AbstractSource $source = null): Attribute
    {
        $source ??= $this->tableSource();
        $attribute = $this->createPartialMock(Attribute::class, ['getSource']);
        $attribute->setIdFieldName('attribute_id');
        $attribute->setData(['attribute_id' => $id, 'store_id' => $storeId, 'frontend_input' => 'select']);
        $attribute->method('getSource')->willReturn($source);
        $source->setAttribute($attribute);
        return $attribute;
    }

    /**
     * Expect exactly one store-filtered option collection with the source ordering.
     *
     * @param int $storeId
     * @param array $ids
     * @param array $options
     * @return Collection
     */
    private function collection(int $storeId, array $ids, array $options): Collection
    {
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())->method('setPositionOrder')->with('asc')->willReturnSelf();
        $collection->expects($this->once())->method('setAttributeFilter')->with(['in' => $ids])->willReturnSelf();
        $collection->expects($this->once())->method('setStoreFilter')->with($storeId)->willReturnSelf();
        $collection->expects($this->once())->method('getIterator')->willReturn(new ArrayIterator($options));
        return $collection;
    }
}
