<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
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
     * Authorize saving of a product.
     *
     * @throws AuthorizationException
     * @throws NoSuchEntityException When product with invalid ID given.
     * @param ProductInterface|Product $product
     * @return void
     */
    public function authorizeSavingOf(ProductInterface $product): void
    {
        if (!$this->authorization->isAllowed('Magento_Catalog::edit_product_design')) {
            $notAllowed = false;
            if (!$product->getId()) {
                if ($product->getData('custom_design')
                    || $product->getData('page_layout')
                    || $product->getData('options_container')
                    || $product->getData('custom_layout_update')
                    || $product->getData('custom_design_from')
                    || $product->getData('custom_design_to')
                ) {
                    $notAllowed = true;
                }
            } else {
                /** @var Product $savedProduct */
                $savedProduct = $this->productFactory->create();
                $savedProduct->load($product->getId());
                if ($savedProduct->getSku()) {
                    throw NoSuchEntityException::singleField('id', $product->getId());
                }
                if ($product->getData('custom_design') != $savedProduct->getData('custom_design')
                    || $product->getData('page_layout') != $savedProduct->getData('page_layout')
                    || $product->getData('options_container') != $savedProduct->getData('options_container')
                    || $product->getData('custom_layout_update') != $savedProduct->getData('custom_layout_update')
                    || $product->getData('custom_design_from') != $savedProduct->getData('custom_design_from')
                    || $product->getData('custom_design_to') != $savedProduct->getData('custom_design_to')
                ) {
                    $notAllowed = true;
                }
            }

            if ($notAllowed) {
                throw new AuthorizationException(__('Not allowed to edit the product\'s design attributes'));
            }
        }
    }
}
