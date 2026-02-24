<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product\Option\Repository as ProductOptionRepository;
use Magento\Catalog\Model\Product\Option\Value as ProductOptionValueModel;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * DataProvider for grid on Import Custom Options modal panel
 *
 * @api
 * @since 101.0.0
 */
class ProductCustomOptionsDataProvider extends ProductDataProvider
{
    /**
     * @var RequestInterface
     * @since 101.0.0
     */
    protected $request;

    /**
     * @var ProductOptionRepository
     * @since 101.0.0
     */
    protected $productOptionRepository;

    /**
     * @var ProductOptionValueModel
     * @since 101.0.0
     */
    protected $productOptionValueModel;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param ProductOptionRepository $productOptionRepository
     * @param ProductOptionValueModel $productOptionValueModel
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $modifiersPool
     * @param MetadataPool|null $metadataPool
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ProductOptionRepository $productOptionRepository,
        ProductOptionValueModel $productOptionValueModel,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = [],
        ?PoolInterface $modifiersPool = null,
        ?MetadataPool $metadataPool = null
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data,
            $modifiersPool
        );

        $this->request = $request;
        $this->productOptionRepository = $productOptionRepository;
        $this->productOptionValueModel = $productOptionValueModel;
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()
            ->get(MetadataPool::class);
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $currentProductId = (int)$this->request->getParam('current_product_id');

            if (0 !== $currentProductId) {
                $this->getCollection()->getSelect()->where('e.entity_id != ?', $currentProductId);
            }

            try {
                $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
                $linkField = $entityMetadata->getLinkField();
            } catch (\Exception $e) {
                $linkField = 'entity_id';
            }

            $this->getCollection()->getSelect()->distinct()->join(
                ['opt' => $this->getCollection()->getTable('catalog_product_option')],
                'opt.product_id = e.' . $linkField,
                null
            );
            $this->getCollection()->load();

            /** @var ProductInterface $product */
            foreach ($this->getCollection() as $product) {
                $options = [];

                /** @var ProductOption|DataObject $option */
                foreach ($this->productOptionRepository->getProductOptions($product) as $option) {
                    $option->setData(
                        'values',
                        $this->productOptionValueModel->getValuesCollection($option)->toArray()['items']
                    );

                    $options[] = $option->toArray();
                }

                $product->setOptions($options);
            }
        }

        $items = $this->getCollection()->toArray();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }
}
