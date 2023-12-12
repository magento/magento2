<?php
namespace Magento\Catalog\Test\Fixture;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class ProductStock implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'prod_id' => null,
        'prod_qty' => 1
    ];

    /**
     * @var DataObjectFactory
     */
    protected DataObjectFactory $dataObjectFactory;

    /**
     * @var StockRegistryInterface
     */
    protected StockRegistryInterface $stockRegistry;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        StockRegistryInterface $stockRegistry
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as ReduceProductStock::DEFAULT_DATA
     */
    public function apply(array $data = []): ?DataObject
    {
        $stockItem = $this->stockRegistry->getStockItem($data['prod_id']);
        $stockItem->setData('is_in_stock', 1);
        $stockItem->setData('qty', 90);
        $stockItem->setData('manage_stock', 1);
        $stockItem->save();

        return $this->dataObjectFactory->create(['data' => [$data]]);
    }
}
