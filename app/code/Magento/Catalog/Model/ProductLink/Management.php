<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Api\ProductLinkManagementInterface;

/**
 * Manage product links from api
 */
class Management implements ProductLinkManagementInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var LinkTypeProvider
     */
    protected $linkTypeProvider;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param LinkTypeProvider $linkTypeProvider
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        LinkTypeProvider $linkTypeProvider
    ) {
        $this->productRepository = $productRepository;
        $this->linkTypeProvider = $linkTypeProvider;
    }

    /**
     * @inheritdoc
     */
    public function getLinkedItemsByType($sku, $type)
    {
        $output = [];

        $linkTypes = $this->linkTypeProvider->getLinkTypes();

        if (!isset($linkTypes[$type])) {
            throw new NoSuchEntityException(
                __('The "%1" link type is unknown. Verify the type and try again.', (string)$type)
            );
        }
        $product = $this->productRepository->get($sku);
        $links = $product->getProductLinks();

        // Only return the links of type specified
        foreach ($links as $link) {
            if ($link->getLinkType() == $type) {
                $output[] = $link;
            }
        }

        return $output;
    }

    /**
     * @inheritdoc
     */
    public function setProductLinks($sku, array $items)
    {

        if (empty($items)) {
            throw InputException::invalidFieldValue('items', 'empty array');
        }

        $linkTypes = $this->linkTypeProvider->getLinkTypes();

        // Check if product link type is set and correct
        foreach ($items as $newLink) {
            $type = $newLink->getLinkType();
            if ($type == null) {
                throw InputException::requiredField("linkType");
            }
            if (!isset($linkTypes[$type])) {
                throw new NoSuchEntityException(
                    __('The "%1" link type wasn\'t found. Verify the type and try again.', $type)
                );
            }
        }

        $product = $this->productRepository->get($sku);

        $existingLinks = $product->getProductLinks();
        $newLinks = array_merge($existingLinks, $items);

        $product->setProductLinks($newLinks);
        try {
            $this->productRepository->save($product);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('The linked products data is invalid. Verify the data and try again.')
            );
        }

        return true;
    }
}
