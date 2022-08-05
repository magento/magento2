<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Fixture;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\ProductRepositoryInterface;
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
     * @var Type
     */
    private Type $productType;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepositoryInterface $productRepository
     * @param DataObjectFactory $dataObjectFactory
     * @param Type $productType
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        DataObjectFactory $dataObjectFactory,
        Type $productType
    ) {
        parent::__construct($cartRepository, $productRepository, $dataObjectFactory);
        $this->productRepository = $productRepository;
        $this->productType = $productType;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * $data['selections'] can be supplied in following formats:
     *      - [["$product1.id$"], ["$product2.id$"]]
     *      - [[{"product_id":"$product1.id$","qty":1}], [{"product_id":"$product2.id$","qty":1}]]
     *      - To skip an option, pass empty array [["$product1.id$"], [], ["$product2.id$"]]
     * <pre>
     *    $data = [
     *      'cart_id'       => (int) Cart ID. Required.
     *      'product_id'    => (int) Product ID. Required.
     *      'selections'    => (array) array of options selections. Required.
     *      'qty'           => (int) Quantity. Optional. Default: 1.
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $bundleProduct = $this->productRepository->getById($data['product_id']);
        $buyRequest = [
            'bundle_option' => [],
            'bundle_option_qty' => [],
            'qty' => $data['qty'] ?? 1,
        ];
        $options = $this->productType->getOptionsCollection($bundleProduct);
        $selections = $this->productType->getSelectionsCollection([], $bundleProduct);
        $options->appendSelections($selections, true);
        $optionsList = array_values($options->getItems());
        foreach ($data['selections'] as $index => $selections) {
            if (!empty($selections)) {
                $option = $optionsList[$index];
                foreach ($selections as $item) {
                    if (is_array($item)) {
                        $productId = (int)$item['product_id'];
                        $qty = $item['qty'] ?? 1;
                    } else {
                        $productId = (int)$item;
                        $qty = 1;
                    }
                    foreach ($option->getSelections() as $selection) {
                        if (((int)$selection->getProductId()) === $productId) {
                            $buyRequest['bundle_option'][$option->getId()][] = $selection->getSelectionId();
                            $buyRequest['bundle_option_qty'][$option->getId()][$selection->getSelectionId()] = $qty;
                            break;
                        }
                    }
                }
            }
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
