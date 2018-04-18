<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Plugin;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\InputException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class AroundProductRepositorySave
 */
class AroundProductRepositorySave
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
     * @param ProductRepositoryInterface $subject
     * @param callable $proceed
     * @param ProductInterface $product
     * @param bool $saveOptions
     * @return ProductInterface
     * @throws CouldNotSaveException
     * @throws InputException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        ProductRepositoryInterface $subject,
        \Closure $proceed,
        ProductInterface $product,
        $saveOptions = false
    ) {
        /** @var ProductInterface $result */
        $result = $proceed($product, $saveOptions);
        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            return $result;
        }

        $extensionAttributes = $result->getExtensionAttributes();
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
        $product->getTypeInstance()->resetConfigurableAttributes($product);

        return $product;
    }

    /**
     * @param array $attributeCodes
     * @param array $linkIds
     * @return $this
     * @throws InputException
     */
    private function validateProductLinks(array $attributeCodes, array $linkIds)
    {
        $valueMap = [];

        foreach ($linkIds as $productId) {
            $variation = $this->productFactory->create()->load($productId);
            $valueKey = '';
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
