<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Model\Link;

use Magento\Downloadable\Api\LinkRepositoryInterface as LinkRepository;

/**
 * Class SaveHandler
 */
class SaveHandler
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
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity)
    {
        if ($entity->getTypeId() !== 'downloadable') {
            return $entity;
        }
        /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
        foreach ($this->linkRepository->getList($entity->getSku()) as $link) {
            $this->linkRepository->delete($link->getId());
        }
        $links = $entity->getExtensionAttributes()->getDownloadableProductLinks() ?: [];
        foreach ($links as $link) {
            $this->linkRepository->save($entity->getSku(), $link, !(bool)$entity->getStoreId());
        }
        return $entity;
    }
}
