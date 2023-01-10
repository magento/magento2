<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\ProductLink\Data\ListCriteria;
use Magento\Catalog\Model\ProductLink\ProductLinkQuery;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\BatchServiceContractResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ResolveRequestInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;

/**
 * Format the product links information to conform to GraphQL schema representation
 */
class BatchProductLinks implements BatchServiceContractResolverInterface
{
    /**
     * @var string[]
     */
    private $linkTypes;

    /**
     * @param array $linkTypes
     */
    public function __construct(array $linkTypes)
    {
        $this->linkTypes = $linkTypes;
    }

    /**
     * @inheritDoc
     */
    public function getServiceContract(): array
    {
        return [ProductLinkQuery::class, 'search'];
    }

    /**
     * @inheritDoc
     */
    public function convertToServiceArgument(ResolveRequestInterface $request)
    {
        $value = $request->getValue();
        if (empty($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $value['model'];

        return new ListCriteria((string)$product->getId(), $this->linkTypes, $product);
    }

    /**
     * @inheritDoc
     */
    public function convertFromServiceResult($result, ResolveRequestInterface $request)
    {
        /** @var \Magento\Catalog\Model\ProductLink\Data\ListResultInterface $result */
        if ($result->getError()) {
            //If model isn't there previous method would've thrown an exception.
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $request->getValue()['model'];
            throw new LocalizedException(
                __('Failed to retrieve product links for "%1"', $product->getSku()),
                $result->getError()
            );
        }

        return array_filter(
            array_map(
                function (ProductLinkInterface $link) {
                    return [
                        'sku' => $link->getSku(),
                        'link_type' => $link->getLinkType(),
                        'linked_product_sku' => $link->getLinkedProductSku(),
                        'linked_product_type' => $link->getLinkedProductType(),
                        'position' => $link->getPosition()
                    ];
                },
                $result->getResult()
            )
        );
    }
}
