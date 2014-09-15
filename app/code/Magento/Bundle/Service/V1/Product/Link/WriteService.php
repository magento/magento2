<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Service\V1\Product\Link;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Webapi\Exception;

class WriteService implements WriteServiceInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Bundle\Model\SelectionFactory $bundleSelection
     */
    protected $bundleSelection;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Bundle\Model\Resource\BundleFactory
     */
    protected $bundleFactory;

    /**
     * @var \Magento\Bundle\Model\Resource\Option\CollectionFactory
     */
    protected $optionCollection;

    /**
     * @param ProductRepository $productRepository
     * @param \Magento\Bundle\Model\SelectionFactory $bundleSelection
     * @param \Magento\Bundle\Model\Resource\BundleFactory $bundleFactory
     * @param \Magento\Bundle\Model\Resource\Option\CollectionFactory $optionCollection,
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductRepository $productRepository,
        \Magento\Bundle\Model\SelectionFactory $bundleSelection,
        \Magento\Bundle\Model\Resource\BundleFactory $bundleFactory,
        \Magento\Bundle\Model\Resource\Option\CollectionFactory $optionCollection,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->bundleSelection = $bundleSelection;
        $this->bundleFactory = $bundleFactory;
        $this->optionCollection = $optionCollection;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild($productSku, $optionId, \Magento\Bundle\Service\V1\Data\Product\Link $linkedProduct)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($productSku);
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new InputException('Product with specified sku: "%1" is not a bundle product', [$productSku]);
        }

        $options = $this->optionCollection->create();
        $options->setProductIdFilter($product->getId())->joinValues($this->storeManager->getStore()->getId());
        $isNewOption = true;
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($options as $option) {
            if ($option->getOptionId() == $optionId) {
                $isNewOption = false;
                break;
            }
        }

        if ($isNewOption) {
            throw new InputException(
                'Product with specified sku: "%1" does not contain option: "%2"',
                [$productSku, $optionId]
            );
        }

        /* @var $resource \Magento\Bundle\Model\Resource\Bundle */
        $resource = $this->bundleFactory->create();
        $selections = $resource->getSelectionsData($product->getId());
        /** @var \Magento\Catalog\Model\Product $linkProductModel */
        $linkProductModel = $this->productRepository->get($linkedProduct->getSku());
        if ($linkProductModel->isComposite()) {
            throw new InputException('Bundle product could not contain another composite product');
        }
        if ($selections) {
            foreach ($selections as $selection) {
                if ($selection['option_id'] = $optionId &&
                    $selection['product_id'] == $linkProductModel->getId()) {
                    throw new CouldNotSaveException(
                        'Child with specified sku: "%1" already assigned to product: "%2"',
                        [$linkedProduct->getSku(), $productSku]
                    );
                }
            }
        }

        $selectionModel = $this->bundleSelection->create();
        $selectionModel->setOptionId($optionId)
            ->setPosition($linkedProduct->getPosition())
            ->setSelectionQty($linkedProduct->getQty())
            ->setSelectionPriceType($linkedProduct->getPriceType())
            ->setSelectionPriceValue($linkedProduct->getPrice())
            ->setSelectionCanChangeQty($linkedProduct->getCanChangeQuantity())
            ->setProductId($linkProductModel->getId())
            ->setParentProductId($product->getId())
            ->setIsDefault($linkedProduct->isDefault())
            ->setWebsiteId($this->storeManager->getStore()->getWebsiteId());

        try {
            $selectionModel->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not save child: "%1"', [$e->getMessage()], $e);
        }

        return $selectionModel->getId();
    }

    /**
     * @inheritdoc
     */
    public function removeChild($productSku, $optionId, $childSku)
    {
        $product = $this->productRepository->get($productSku);

        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new Exception(
                sprintf('Product with specified sku: %s is not a bundle product', $productSku),
                Exception::HTTP_FORBIDDEN
            );
        }

        $excludeSelectionIds = array();
        $usedProductIds = array();
        $removeSelectionIds = array();
        foreach ($this->getOptions($product) as $option) {
            foreach ($option->getSelections() as $selection) {
                if ((strcasecmp($selection->getSku(), $childSku) == 0) && ($selection->getOptionId() == $optionId)) {
                    $removeSelectionIds[] = $selection->getSelectionId();
                    continue;
                }
                $excludeSelectionIds[] = $selection->getSelectionId();
                $usedProductIds[] = $selection->getProductId();
            }
        }
        if (empty($removeSelectionIds)) {
            throw new NoSuchEntityException('Requested bundle option product doesn\'t exist');
        }
        /* @var $resource \Magento\Bundle\Model\Resource\Bundle */
        $resource = $this->bundleFactory->create();
        $resource->dropAllUnneededSelections($product->getId(), $excludeSelectionIds);
        $resource->saveProductRelations($product->getId(), array_unique($usedProductIds));

        return true;
    }

    /**
     * @param Product $product
     * @return Option[]
     */
    private function getOptions(Product $product)
    {
        $product->getTypeInstance()->setStoreFilter($product->getStoreId(), $product);

        $optionCollection = $product->getTypeInstance()->getOptionsCollection($product);

        $selectionCollection = $product->getTypeInstance()->getSelectionsCollection(
            $product->getTypeInstance()->getOptionsIds($product),
            $product
        );

        return $optionCollection->appendSelections($selectionCollection);
    }
}
