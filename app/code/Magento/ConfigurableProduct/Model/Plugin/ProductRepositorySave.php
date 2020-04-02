<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Validating product links of configurable product and reset configurable attributes after save configurable product
 */
class ProductRepositorySave
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param ProductFactory $productFactory
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductFactory $productFactory
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productFactory = $productFactory;
    }

    /**
     * Reset configurable attributes to configurable product
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $result
     * @param ProductInterface $product
     * @param bool $saveOptions
     * @return ProductInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        ProductRepositoryInterface $subject,
        ProductInterface $result,
        ProductInterface $product,
        $saveOptions = false
    ) {
        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            return $result;
        }
        $result->getTypeInstance()->resetConfigurableAttributes($product);

        return $result;
    }

    /**
     * Validate product links of configurable product
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $product
     * @param bool $saveOptions
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        ProductRepositoryInterface $subject,
        ProductInterface $product,
        $saveOptions = false
    ) {
        $result[] = $product;
        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            return $result;
        }

        $extensionAttributes = $product->getExtensionAttributes();
        if ($extensionAttributes === null) {
            return $result;
        }

        $configurableLinks = (array) $extensionAttributes->getConfigurableProductLinks();
        $configurableOptions = (array) $extensionAttributes->getConfigurableProductOptions();

        if (empty($configurableLinks) && empty($configurableOptions)) {
            return $result;
        }

        $attributeCodes = [];
        /** @var OptionInterface $configurableOption */
        foreach ($configurableOptions as $configurableOption) {
            $eavAttribute = $this->productAttributeRepository->get($configurableOption->getAttributeId());
            $attributeCode = $eavAttribute->getAttributeCode();
            $attributeCodes[] = $attributeCode;
        }
        $this->validateProductLinks($attributeCodes, $configurableLinks);
        return $result;
    }

    /**
     * Validate required attributes and validate the same set of attribute values
     *
     * @param array $attributeCodes
     * @param array $linkIds
     * @return void
     * @throws InputException
     * @throws LocalizedException
     */
    private function validateProductLinks(array $attributeCodes, array $linkIds)
    {
        $valueMap = [];

        foreach ($linkIds as $productId) {
            $variation = $this->productFactory->create()->load($productId);
            $valueKey = '';
            if ($variation->getId() === null) {
                throw new LocalizedException(
                    __('Product with id "%1" does not exist.', $productId)
                );
            }
            foreach ($attributeCodes as $attributeCode) {
                if (!$variation->getData($attributeCode)) {
                    throw new InputException(
                        __('Product with id "%1" does not contain required attribute "%2".', $productId, $attributeCode)
                    );
                }
                $valueKey = $valueKey . $attributeCode . ':' . $variation->getData($attributeCode) . ';';
            }
            if (isset($valueMap[$valueKey])) {
                throw new InputException(
                    __(
                        'Products "%1" and "%2" have the same set of attribute values.',
                        $productId,
                        $valueMap[$valueKey]
                    )
                );
            }
            $valueMap[$valueKey] = $productId;
        }
    }
}
