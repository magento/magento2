<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Helper\ImageFactory as CatalogImageHelperFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Returns product's image. If the image is not set, returns a placeholder
 */
class Image implements ResolverInterface
{
    /**
     * @var CatalogImageHelperFactory
     */
    private $catalogImageHelperFactory;

    /**
     * @param CatalogImageHelperFactory $catalogImageHelperFactory
     */
    public function __construct(
        CatalogImageHelperFactory $catalogImageHelperFactory
    ) {
        $this->catalogImageHelperFactory = $catalogImageHelperFactory;
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
    ): array {
        if (!isset($value['model'])) {
            throw new \LogicException(__("Cannot resolve entity model"));
        }
        /** @var Product $product */
        $product = $value['model'];
        $imageType = $field->getName();

        $catalogImageHelper = $this->catalogImageHelperFactory->create();
        $imageUrl = $catalogImageHelper->init(
            $product,
            'product_' . $imageType,
            ['type' => $imageType]
        )->getUrl();

        return [
            'url' => $imageUrl,
            'path' => $product->getData($imageType)
        ];
    }
}
