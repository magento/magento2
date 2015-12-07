<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Link\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\Framework\Model\Entity\MetadataPool;

/**
 * Class SaveProductLinks
 */
class SaveProductLinks
{
    /**
     * @var ProductLinkRepositoryInterface
     */
    protected $productLinkRepository;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Link
     */
    private $linkResource;

    /**
     * @param MetadataPool $metadataPool
     * @param Link $linkResource
     */
    public function __construct(
        MetadataPool $metadataPool,
        Link $linkResource,
        ProductLinkRepositoryInterface $productLinkRepository
    ) {

        $this->metadataPool = $metadataPool;
        $this->linkResource = $linkResource;
        $this->productLinkRepository = $productLinkRepository;
    }

    /**
     * @param $product
     * @param $data
     * @param $typeId
     * @return mixed
     * @throws \Exception
     */
    public function execute($product, $data, $typeId)
    {
        //TODO remove after upsell, crosssell refactored
        foreach($data as $link) {
            $this->productLinkRepository->save($link);
        }
        return $product;
    }
}
