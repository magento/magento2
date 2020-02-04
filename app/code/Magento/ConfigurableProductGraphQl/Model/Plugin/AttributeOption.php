<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\AttributeOptionProvider;

/**
 * Class AttributeOption
 *
 *
 */
class AttributeOption
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param AttributeOptionProvider $subject
     * @param $result
     * @return mixed
     */
    public function afterGetAttributeOptions(
        AttributeOptionProvider $subject,
        $result
    ) {
        foreach($result as $key => $option){
            $product = $this->productRepository->get($option['sku']);

            if ($product->getStatus() == Status::STATUS_DISABLED) {
                unset($result[$key]);
            }
        }

        return $result;
    }
}
