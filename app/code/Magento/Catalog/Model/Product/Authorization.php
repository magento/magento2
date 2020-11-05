<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\Product\Attribute\Backend\LayoutUpdate;

/**
 * Additional authorization for product operations.
 */
class Authorization
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @param AuthorizationInterface $authorization
     * @param ProductFactory $factory
     */
    public function __construct(AuthorizationInterface $authorization, ProductFactory $factory)
    {
        $this->authorization = $authorization;
        $this->productFactory = $factory;
    }

    /**
     * Extract attribute value from the model.
     *
     * @param ProductModel $product
     * @param AttributeInterface $attr
     * @return mixed
     * @throws \RuntimeException When no new value is present.
     */
    private function extractAttributeValue(ProductModel $product, AttributeInterface $attr)
    {
        if ($product->hasData($attr->getAttributeCode())) {
            $newValue = $product->getData($attr->getAttributeCode());
        } elseif ($product->hasData(ProductModel::CUSTOM_ATTRIBUTES)
            && $attrValue = $product->getCustomAttribute($attr->getAttributeCode())
        ) {
            $newValue = $attrValue->getValue();
        } else {
            throw new \RuntimeException('No new value is present');
        }

        if (empty($newValue)
            || ($attr->getBackend() instanceof LayoutUpdate
                && ($newValue === LayoutUpdate::VALUE_USE_UPDATE_XML || $newValue === LayoutUpdate::VALUE_NO_UPDATE)
            )
        ) {
            $newValue = null;
        }

        return $newValue;
    }

    /**
     * Prepare old values to compare to.
     *
     * @param AttributeInterface $attribute
     * @param array|null $oldProduct
     * @return array
     */
    private function fetchOldValues(AttributeInterface $attribute, ?array $oldProduct): array
    {
        $attrCode = $attribute->getAttributeCode();
        if ($oldProduct) {
            //New value may only be the saved value
            $oldValues = [!empty($oldProduct[$attrCode]) ? $oldProduct[$attrCode] : null];
            if (empty($oldValues[0])) {
                $oldValues[0] = null;
            }
        } else {
            //New value can be empty or default
            $oldValues[] = $attribute->getDefaultValue();
        }

        return $oldValues;
    }

    /**
     * Check whether the product has changed.
     *
     * @param ProductModel $product
     * @param array|null $oldProduct
     * @return bool
     */
    private function hasProductChanged(ProductModel $product, ?array $oldProduct = null): bool
    {
        $designAttributes = [
            'custom_design',
            'page_layout',
            'options_container',
            'custom_layout_update',
            'custom_design_from',
            'custom_design_to',
            'custom_layout_update_file'
        ];
        $attributes = $product->getAttributes();

        foreach ($designAttributes as $designAttribute) {
            if (!array_key_exists($designAttribute, $attributes)) {
                continue;
            }
            $attribute = $attributes[$designAttribute];
            $oldValues = $this->fetchOldValues($attribute, $oldProduct);
            try {
                $newValue = $this->extractAttributeValue($product, $attribute);
            } catch (\RuntimeException $exception) {
                //No new value
                continue;
            }
            if (!in_array($newValue, $oldValues, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Authorize saving of a product.
     *
     * @throws AuthorizationException
     * @throws NoSuchEntityException When product with invalid ID given.
     * @param ProductInterface|ProductModel $product
     * @return void
     */
    public function authorizeSavingOf(ProductInterface $product): void
    {
        if (!$this->authorization->isAllowed('Magento_Catalog::edit_product_design')) {
            $oldData = null;
            if ($product->getId()) {
                if ($product->getOrigData()) {
                    $oldData = $product->getOrigData();
                } else {
                    /** @var ProductModel $savedProduct */
                    $savedProduct = $this->productFactory->create();
                    $savedProduct->load($product->getId());
                    if (!$savedProduct->getSku()) {
                        throw NoSuchEntityException::singleField('id', $product->getId());
                    }
                    $oldData = $savedProduct->getData();
                }
            }
            if ($this->hasProductChanged($product, $oldData)) {
                throw new AuthorizationException(__('Not allowed to edit the product\'s design attributes'));
            }
        }
    }
}
