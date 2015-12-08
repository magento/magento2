<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Model\Link;

use Magento\Downloadable\Api\LinkRepositoryInterface as LinkRepository;

/**
 * Class ReadHandler
 */
class ReadHandler
{
    /**
     * @var LinkRepository
     */
    protected $linkRepository;

    /**
     * @param LinkRepository $linkRepository
     */
    public function __construct(LinkRepository $linkRepository)
    {
        $this->linkRepository = $linkRepository;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity)
    {
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        if ($entity->getTypeId() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $entity;
        }
        $entityExtension = $entity->getExtensionAttributes();
        $links = $this->linkRepository->getLinksByProduct($entity);
        if ($links) {
            $entityExtension->setDownloadableProductLinks($links);
        }
        $entity->setExtensionAttributes($entityExtension);
        return $entity;
    }
}
