<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Plugin\Quote;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProductGraphQl\Model\Cart\BuyRequest\SuperAttributeDataProvider;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\Quote;

/**
 * Intercepts data before update cart and check customized options available
 * and update super attribute data for configurable product
 */
class UpdateCustomizedOptions
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SuperAttributeDataProvider
     */
    private $superAttributeDataProvider;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SuperAttributeDataProvider $superAttributeDataProvider
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SuperAttributeDataProvider $superAttributeDataProvider
    ) {
        $this->productRepository = $productRepository;
        $this->superAttributeDataProvider = $superAttributeDataProvider;
    }

    /**
     * Parses the product data after update cart event and checking customized options super attribute
     *
     * @param Quote $subject
     * @param int $itemId
     * @param DataObject $buyRequest
     * @param null|array|DataObject $params
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeUpdateItem(
        Quote $subject,
        int $itemId,
        DataObject $buyRequest,
        ?DataObject $params = null
    ): void {
        $item = $subject->getItemById($itemId);
        if ($item) {
            $productId = $item->getProduct()->getId();
            $product = clone $this->productRepository->getById($productId, false, $subject->getStore()->getId());
            if ($item->getProductType() === Configurable::TYPE_CODE && count($product->getOptions()) > 0) {
                $cartItemData = [];
                $cartItemData['model'] = $subject;
                if (count($item->getChildren()) > 0) {
                    $currentItem = current($item->getChildren());
                    $cartItemData['data']['sku'] = $currentItem->getSku();
                } else {
                    $cartItemData['data']['sku'] = $item->getSku();
                }
                $cartItemData['data']['quantity'] = $buyRequest->getQty();
                $cartItemData['parent_sku'] = $product->getSku();
                $superAttributeDetails = $this->superAttributeDataProvider->execute($cartItemData);
                $buyRequest->addData($superAttributeDetails);
            }
        }
    }
}
