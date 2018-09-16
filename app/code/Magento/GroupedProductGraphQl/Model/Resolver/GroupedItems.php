<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProductGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GroupedProduct\Model\Product\Initialization\Helper\ProductLinks\Plugin\Grouped;

/**
 * @inheritdoc
 */
class GroupedItems implements ResolverInterface
{
    /**
     * @var Product
     */
    private $productResolver;

    /**
     * @param Product $productResolver
     */
    public function __construct(
        Product $productResolver
    ) {
        $this->productResolver = $productResolver;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('"model" value should be specified'));
        }

        $data = [];
        $productModel = $value['model'];
        $links = $productModel->getProductLinks();
        foreach ($links as $link) {
            if ($link->getLinkType() !== Grouped::TYPE_NAME) {
                continue;
            }

            $data[] = [
                'position' => (int)$link->getPosition(),
                'qty' => $link->getExtensionAttributes()->getQty(),
                'sku' => $link->getLinkedProductSku()
            ];
        }

        return $data;
    }
}
