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
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * Check whether the product has changed.
     *
     * @param ProductModel $product
     * @param ProductModel|null $oldProduct
     * @return bool
     */
    private function hasProductChanged(ProductModel $product, ?ProductModel $oldProduct = null): bool
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
        $attributes = null;
        if (!$oldProduct) {
            //For default values.
            $attributes = $product->getAttributes();
        }
        foreach ($designAttributes as $designAttribute) {
            $oldValues = [null];
            if ($oldProduct) {
                //New value may only be the saved value
                $oldValues = [$oldProduct->getData($designAttribute)];
            } elseif (array_key_exists($designAttribute, $attributes)) {
                //New value can be empty or default
                $oldValues[] = $attributes[$designAttribute]->getDefaultValue();
            }
            $newValue = $product->getData($designAttribute);
            if (empty($newValue)) {
                $newValue = null;
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
            $savedProduct = null;
            if ($product->getId()) {
                /** @var ProductModel $savedProduct */
                $savedProduct = $this->productFactory->create();
                $savedProduct->load($product->getId());
                if (!$savedProduct->getSku()) {
                    throw NoSuchEntityException::singleField('id', $product->getId());
                }
            }
            if ($this->hasProductChanged($product, $savedProduct)) {
                throw new AuthorizationException(__('Not allowed to edit the product\'s design attributes'));
            }
        }
    }
}
