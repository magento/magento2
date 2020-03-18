<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Plugin\Model\ResourceModel;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Indexer\ActionInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;

/**
 * Plugin product resource model
 */
class Product
{
    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var ActionInterface
     */
    private $productIndexer;

    /**
     * Initialize Product dependencies.
     *
     * @param Configurable $configurable
     * @param ActionInterface $productIndexer
     */
    public function __construct(
        Configurable $configurable,
        ActionInterface $productIndexer
    ) {
        $this->configurable = $configurable;
        $this->productIndexer = $productIndexer;
    }

    /**
     * We need reset attribute set id to attribute after related simple product was saved
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product $subject
     * @param \Magento\Framework\DataObject $object
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Magento\Framework\DataObject $object
    ) {
        /** @var \Magento\Catalog\Model\Product $object */
        if ($object->getTypeId() == Configurable::TYPE_CODE) {
            $object->getTypeInstance()->getSetAttributes($object);
            $this->resetConfigurableOptionsData($object);
        }
    }

    /**
     * Set null for configurable options attribute of configurable product
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function resetConfigurableOptionsData($object)
    {
        $extensionAttribute = $object->getExtensionAttributes();
        if ($extensionAttribute && $extensionAttribute->getConfigurableProductOptions()) {
            /** @var ProductAttributeRepositoryInterface $productAttributeRepository */
            $productAttributeRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(ProductAttributeRepositoryInterface::class);
            /** @var OptionInterface $option */
            foreach ($extensionAttribute->getConfigurableProductOptions() as $option) {
                $eavAttribute = $productAttributeRepository->get($option->getAttributeId());
                $object->setData($eavAttribute->getAttributeCode(), null);
            }
        }
    }

    /**
     * Gather configurable parent ids of product being deleted and reindex after delete is complete.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\ResourceModel\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $configurableProductIds = $this->configurable->getParentIdsByChild($product->getId());
        $result = $proceed($product);
        $this->productIndexer->executeList($configurableProductIds);

        return $result;
    }
}
