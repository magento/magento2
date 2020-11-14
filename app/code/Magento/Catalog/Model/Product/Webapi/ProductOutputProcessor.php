<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Webapi;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Webapi\Request;
use Magento\Framework\Webapi\Rest\Request\DeserializerInterface;

/**
 * Class for checking empty array and remove it from the output result
 */
class ProductOutputProcessor
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var DeserializerInterface
     */
    private $deserializer;

    /**
     * @param Request $request
     * @param DeserializerInterface $deserializer
     */
    public function __construct(
        Request $request,
        DeserializerInterface $deserializer
    ) {
        $this->request = $request;
        $this->deserializer = $deserializer;
    }

    /**
     * Removing attribute from the result array if its null or empty
     *
     * @param ProductInterface $product
     * @param array $result
     * @return array
     */
    public function execute(
        ProductInterface $product,
        array $result
    ): array {
        $requestContent = $this->request->getContent() ?? [];
        if (empty($requestContent)) {
            return $result;
        }
        $requestContentDetails = (array)$this->deserializer->deserialize($requestContent);
        $requestProductList = $this->extractProductList($requestContentDetails);

        $requestProductList = array_filter(
            $requestProductList,
            function ($requestProduct) use ($product) {
                return isset($requestProduct['sku']) && $requestProduct['sku'] === $product->getSku();
            }
        );

        if (empty($requestProductList)) {
            return $result;
        }

        $requestProduct = current($requestProductList);

        if (empty($product->getTierPrices()) && !array_key_exists('tier_prices', $requestProduct)) {
            unset($result['tier_prices']);
        }

        if (empty($product->getProductLinks()) && !array_key_exists('product_links', $requestProduct)) {
            unset($result['product_links']);
        }

        return $result;
    }

    /**
     * Extract product list from the request content details
     *
     * @param array $contentDetails
     * @return array
     */
    private function extractProductList(array $contentDetails): array
    {
        $productList = [];
        $arrayIterator = new \RecursiveArrayIterator($contentDetails);
        $iterator = new \RecursiveIteratorIterator($arrayIterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $iteratorKey => $iteratorValue) {
            if ($iteratorKey === 'product') {
                array_push($productList, $iteratorValue);
            }
        }
        return $productList;
    }
}
