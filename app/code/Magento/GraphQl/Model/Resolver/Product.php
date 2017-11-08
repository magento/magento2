<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use GraphQL\Type\Definition\ResolveInfo;
use Magento\GraphQl\Model\ResolverInterface;
use Magento\Framework\Exception\InputException;

/**
 * Product field resolver, used for GraphQL request processing.
 */
class Product implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var MediaGalleryEntries
     */
    private $mediaGalleryResolver;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param MediaGalleryEntries $mediaGalleryResolver
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ServiceOutputProcessor $serviceOutputProcessor,
        MediaGalleryEntries $mediaGalleryResolver,
        SerializerInterface $jsonSerializer
    ) {
        $this->productRepository = $productRepository;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->mediaGalleryResolver = $mediaGalleryResolver;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $args, ResolveInfo $info)
    {
        if (isset($args['sku'])) {
            return $this->getProduct($args['sku']);
        } elseif (isset($args['id'])) {
            return $this->getProductById($args['id']);
        }

        throw new InputException(__('Missing arguments for correct type resolution.'));
    }

    /**
     * Resolves product by Sku
     *
     * @param string $sku
     * @return array|null
     */
    public function getProduct(string $sku)
    {
        try {
            $productObject = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            // No error should be thrown, null result should be returned
            return null;
        }
        return $this->processProduct($productObject);
    }

    /**
     * Resolves product by Id
     *
     * @param int $productId
     * @return array|null
     */
    public function getProductById(int $productId)
    {
        try {
            $productObject = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            // No error should be thrown, null result should be returned
            return null;
        }
        return $this->processProduct($productObject);
    }

    /**
     * Retrieve single product data in array format
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $productObject
     * @return array|null
     */
    private function processProduct(\Magento\Catalog\Api\Data\ProductInterface $productObject)
    {
        $product = $this->serviceOutputProcessor->process(
            $productObject,
            ProductRepositoryInterface::class,
            'get'
        );
        if (isset($product['extension_attributes'])) {
            $product = array_merge($product, $product['extension_attributes']);
        }
        $customAttributes = [];
        if (isset($product['custom_attributes'])) {
            foreach ($product['custom_attributes'] as $attribute) {
                $isArray = false;
                if (is_array($attribute['value'])) {
                    $isArray = true;
                    foreach ($attribute['value'] as $attributeValue) {
                        if (is_array($attributeValue)) {
                            $customAttributes[$attribute['attribute_code']] = $this->jsonSerializer->serialize(
                                $attribute['value']
                            );
                            continue;
                        }
                        $customAttributes[$attribute['attribute_code']] = implode(',', $attribute['value']);
                        continue;
                    }
                }
                if ($isArray) {
                    continue;
                }
                $customAttributes[$attribute['attribute_code']] = $attribute['value'];
            }
        }
        $product = array_merge($product, $customAttributes);
        $product = array_merge($product, $product['product_links']);
        $product['media_gallery_entries'] = $this
            ->mediaGalleryResolver->getMediaGalleryEntries($productObject->getSku());

        if (isset($product['configurable_product_links'])) {
            $product['configurable_product_links'] = $this
                ->resolveConfigurableProductLinks($product['configurable_product_links']);
        }

        return $product;
    }

    /**
     * Resolve links for configurable product into simple products
     *
     * @param int[]
     * @return array
     */
    private function resolveConfigurableProductLinks($configurableProductLinks)
    {
        if (empty($configurableProductLinks)) {
            return [];
        }
        $result = [];
        foreach ($configurableProductLinks as $key => $id) {
            $result[$key] = $this->getProductById($id);
        }
        return $result;
    }
}
