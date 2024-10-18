<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Fixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Quote\Api\CartRepositoryInterface;

class AddProductToCart extends \Magento\Quote\Test\Fixture\AddProductToCart
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Configurable
     */
    private Configurable $productType;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepositoryInterface $productRepository
     * @param DataObjectFactory $dataObjectFactory
     * @param Configurable $productType
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        DataObjectFactory $dataObjectFactory,
        Configurable $productType
    ) {
        parent::__construct($cartRepository, $productRepository, $dataObjectFactory);
        $this->productRepository = $productRepository;
        $this->productType = $productType;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'cart_id'           => (int) Cart ID. Required.
     *      'product_id'        => (int) Product ID. Required.
     *      'child_product_id'  => (int) Child Product ID. Required.
     *      'qty'               => (int) Quantity. Optional. Default: 1.
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $configurableProduct = $this->productRepository->getById($data['product_id']);
        $childProduct = $this->productRepository->getById($data['child_product_id']);
        $buyRequest = [
            'super_attribute' => [],
            'qty' => $data['qty'] ?? 1,
        ];
        foreach ($this->productType->getUsedProductAttributes($configurableProduct) as $attr) {
            $buyRequest['super_attribute'][$attr->getId()] = $childProduct->getData($attr->getAttributeCode());
        }
        return parent::apply(
            [
                'cart_id' => $data['cart_id'],
                'product_id' => $data['product_id'],
                'buy_request' => $buyRequest
            ]
        );
    }
}
