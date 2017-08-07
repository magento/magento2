<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product\Option\Repository as ProductOptionRepository;
use Magento\Catalog\Model\Product\Option\Value as ProductOptionValueModel;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Framework\DataObject;

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
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 101.0.0
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
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $this->request = $request;
        $this->productOptionRepository = $productOptionRepository;
        $this->productOptionValueModel = $productOptionValueModel;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $currentProductId = (int)$this->request->getParam('current_product_id');

            if (0 !== $currentProductId) {
                $this->getCollection()->getSelect()->where('e.entity_id != ?', $currentProductId);
            }

            $this->getCollection()->getSelect()->distinct()->join(
                ['opt' => $this->getCollection()->getTable('catalog_product_option')],
                'opt.product_id = e.entity_id',
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
