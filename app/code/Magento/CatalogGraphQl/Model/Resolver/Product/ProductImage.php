<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Helper\ImageFactory as CatalogImageHelperFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Return product image paths by image type.
 */
class ProductImage implements ResolverInterface
{
    /**
     * @var CatalogImageHelperFactory
     */
    private $catalogImageHelperFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CatalogImageHelperFactory $catalogImageHelperFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CatalogImageHelperFactory $catalogImageHelperFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->catalogImageHelperFactory = $catalogImageHelperFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get product's image by type.
     *
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
        /** @var Product $product */
        $product = $value['model'];
        $imageType = $field->getName();

        /** @var \Magento\Catalog\Helper\Image $catalogImageHelper */
        $catalogImageHelper = $this->catalogImageHelperFactory->create();

        /** @var \Magento\Catalog\Helper\Image $image */
        $image = $catalogImageHelper->init(
            $product,
            'product_' . $imageType,
            ['type' => $imageType]
        );

        $imageData = [
            'url'   => $image->getUrl(),
            'path'  => $product->getData($imageType),
            'label' => $image->getLabel()
        ];

        return $imageData;
    }
}
