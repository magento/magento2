<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleGraphQl\Model\Resolver\Links;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;

/**
 * {@inheritdoc}
 */
class Product implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param FormatterInterface $formatter
     */
    public function __construct(ProductRepositoryInterface $productRepository, FormatterInterface $formatter)
    {
        $this->productRepository = $productRepository;
        $this->formatter = $formatter;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($value['sku']);
        $data = $this->formatter->format($product);

        return $data;
    }
}
