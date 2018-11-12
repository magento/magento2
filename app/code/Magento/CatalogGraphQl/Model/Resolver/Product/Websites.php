<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\Websites\Collection;

/**
 * Retrieves the websites information object
 */
class Websites implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var Collection
     */
    private $productWebsitesCollection;

    /**
     * @param ValueFactory $valueFactory
     * @param Collection $productWebsitesCollection
     */
    public function __construct(
        ValueFactory $valueFactory,
        Collection $productWebsitesCollection
    ) {
        $this->valueFactory = $valueFactory;
        $this->productWebsitesCollection = $productWebsitesCollection;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['entity_id'])) {
            throw new GraphQlInputException(__('"model" value should be specified'));
        }
        $this->productWebsitesCollection->addIdFilters((int)$value['entity_id']);
        $result = function () use ($value) {
            return $this->productWebsitesCollection->getWebsiteForProductId((int)$value['entity_id']);
        };

        return $this->valueFactory->create($result);
    }
}
