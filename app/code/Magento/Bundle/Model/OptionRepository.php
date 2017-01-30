<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionRepository implements \Magento\Bundle\Api\ProductOptionRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Product\Type
     */
    protected $type;

    /**
     * @var \Magento\Bundle\Api\Data\OptionInterfaceFactory
     */
    protected $optionFactory;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Option
     */
    protected $optionResource;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Bundle\Api\ProductLinkManagementInterface
     */
    protected $linkManagement;

    /**
     * @var Product\OptionList
     */
    protected $productOptionList;

    /**
     * @var Product\LinksList
     */
    protected $linkList;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param Product\Type $type
     * @param \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory
     * @param \Magento\Bundle\Model\ResourceModel\Option $optionResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Bundle\Api\ProductLinkManagementInterface $linkManagement
     * @param Product\OptionList $productOptionList
     * @param Product\LinksList $linkList
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Bundle\Model\Product\Type $type,
        \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory,
        \Magento\Bundle\Model\ResourceModel\Option $optionResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Bundle\Api\ProductLinkManagementInterface $linkManagement,
        \Magento\Bundle\Model\Product\OptionList $productOptionList,
        \Magento\Bundle\Model\Product\LinksList $linkList,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->productRepository = $productRepository;
        $this->type = $type;
        $this->optionFactory = $optionFactory;
        $this->optionResource = $optionResource;
        $this->storeManager = $storeManager;
        $this->linkManagement = $linkManagement;
        $this->productOptionList = $productOptionList;
        $this->linkList = $linkList;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sku, $optionId)
    {
        $product = $this->getProduct($sku);

        /** @var \Magento\Bundle\Model\Option $option */
        $option = $this->type->getOptionsCollection($product)->getItemById($optionId);
        if (!$option || !$option->getId()) {
            throw new NoSuchEntityException(__('Requested option doesn\'t exist'));
        }

        $productLinks = $this->linkList->getItems($product, $optionId);

        /** @var \Magento\Bundle\Api\Data\OptionInterface $option */
        $optionDataObject = $this->optionFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $optionDataObject,
            $option->getData(),
            '\Magento\Bundle\Api\Data\OptionInterface'
        );
        $optionDataObject->setOptionId($option->getId())
            ->setTitle($option->getTitle() === null ? $option->getDefaultTitle() : $option->getTitle())
            ->setSku($product->getSku())
            ->setProductLinks($productLinks);

        return $optionDataObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($sku)
    {
        $product = $this->getProduct($sku);
        return $this->getListByProduct($product);
    }

    /**
     * @param ProductInterface $product
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     */
    public function getListByProduct(ProductInterface $product)
    {
        return $this->productOptionList->getItems($product);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Bundle\Api\Data\OptionInterface $option)
    {
        try {
            $this->optionResource->delete($option);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\StateException(
                __('Cannot delete option with id %1', $option->getOptionId()),
                $exception
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($sku, $optionId)
    {
        $product = $this->getProduct($sku);
        $optionCollection = $this->type->getOptionsCollection($product);
        $optionCollection->setIdFilter($optionId);
        return $this->delete($optionCollection->getFirstItem());
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Bundle\Api\Data\OptionInterface $option
    ) {
        $metadata = $this->getMetadataPool()->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);

        $option->setStoreId($product->getStoreId());
        $option->setParentId($product->getData($metadata->getLinkField()));
        $linksToAdd = [];
        $option->setDefaultTitle($option->getDefaultTitle() ?: $option->getTitle());
        if (is_array($option->getProductLinks())) {
            $linksToAdd = $option->getProductLinks();
        }
        try {
            $this->optionResource->save($option);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save option'), $e);
        }

        /** @var \Magento\Bundle\Api\Data\LinkInterface $linkedProduct */
        foreach ($linksToAdd as $linkedProduct) {
            $this->linkManagement->addChild($product, $option->getOptionId(), $linkedProduct);
        }
        $product->setIsRelationsChanged(true);
        return $option->getOptionId();
    }

    /**
     * Update option selections
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @return $this
     */
    protected function updateOptionSelection(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Bundle\Api\Data\OptionInterface $option
    ) {
        $existingLinks = [];
        $linksToAdd = [];
        $linksToDelete = [];
        if (is_array($option->getProductLinks())) {
            $productLinks = $option->getProductLinks();
            foreach ($productLinks as $productLink) {
                $linksToAdd[] = $productLink;
            }
            /** @var \Magento\Bundle\Api\Data\LinkInterface[] $linksToDelete */
            $linksToDelete = $this->compareLinks([], $existingLinks);
        }
        foreach ($linksToDelete as $linkedProduct) {
            $this->linkManagement->removeChild(
                $product->getSku(),
                $option->getOptionId(),
                $linkedProduct->getSku()
            );
        }
        foreach ($linksToAdd as $linkedProduct) {
            $this->linkManagement->addChild($product, $option->getOptionId(), $linkedProduct);
        }
        return $this;
    }

    /**
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\InputException
     */
    private function getProduct($sku)
    {
        $product = $this->productRepository->get($sku, true);
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new InputException(__('Only implemented for bundle product'));
        }
        return $product;
    }

    /**
     * Computes the difference of arrays
     *
     * @param array $firstArray of \Magento\Bundle\Api\Data\LinkInterface
     * @param array $secondArray of \Magento\Bundle\Api\Data\LinkInterface
     * @return array
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function compareLinks(
        array $firstArray,
        array $secondArray
    ) {
        $result = [];
        if (count($firstArray) < count($secondArray)) {
            $holder = $firstArray;
            $firstArray = $secondArray;
            $secondArray = $holder;
        }
        foreach ($firstArray as $obj) {
            foreach ($secondArray as $objToCompare) {
                if (
                    $obj->getId() != $objToCompare->getId()
                    && $obj instanceof \Magento\Bundle\Api\Data\LinkInterface
                    && $objToCompare instanceof \Magento\Bundle\Api\Data\LinkInterface
                ) {
                    $result[] = $obj;
                }
            }
        }
        return $result;
    }

    /**
     * Get MetadataPool instance
     * @return MetadataPool
     */
    private function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()->get(MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
