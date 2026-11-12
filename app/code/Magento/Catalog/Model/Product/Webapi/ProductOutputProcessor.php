<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Webapi;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Webapi\Request;
use Magento\Framework\Webapi\Rest\Request\DeserializerInterface;

/**
 * Class for checking empty array and remove it from the output result
 */
class ProductOutputProcessor implements ResetAfterRequestInterface
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
     * @var array|null
     */
    private ?array $requestProducts = null;

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
        if ($this->requestProducts === null) {
            $this->requestProducts = $this->getRequestProducts();
        }

        $sku = $product->getSku();
        $key = get_debug_type($sku) . ':' . $sku;
        if (!isset($this->requestProducts[$key])) {
            return $result;
        }

        $requestProduct = $this->requestProducts[$key];

        if (empty($product->getTierPrices()) && !$requestProduct['tier_prices']) {
            unset($result['tier_prices']);
        }

        if (empty($product->getProductLinks()) && !$requestProduct['product_links']) {
            unset($result['product_links']);
        }

        return $result;
    }

    /**
     * Index request field presence by SKU.
     *
     * @return array
     */
    private function getRequestProducts(): array
    {
        $requestContent = $this->request->getContent();
        if (empty($requestContent)) {
            return [];
        }
        $requestProducts = [];
        $requestContentDetails = (array)$this->deserializer->deserialize($requestContent);
        foreach ($this->extractProductList($requestContentDetails) as $requestProduct) {
            if (!isset($requestProduct['sku'])) {
                continue;
            }
            $key = get_debug_type($requestProduct['sku']) . ':' . $requestProduct['sku'];
            if (!isset($requestProducts[$key])) {
                $requestProducts[$key] = [
                    'tier_prices' => array_key_exists('tier_prices', $requestProduct),
                    'product_links' => array_key_exists('product_links', $requestProduct),
                ];
            }
        }
        return $requestProducts;
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

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->requestProducts = null;
    }
}
