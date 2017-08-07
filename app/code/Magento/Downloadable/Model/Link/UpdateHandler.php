<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Link;

use Magento\Downloadable\Api\LinkRepositoryInterface as LinkRepository;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class UpdateHandler
 * @since 2.1.0
 */
class UpdateHandler implements ExtensionInterface
{
    /**
     * @var LinkRepository
     * @since 2.1.0
     */
    protected $linkRepository;

    /**
     * @param LinkRepository $linkRepository
     * @since 2.1.0
     */
    public function __construct(LinkRepository $linkRepository)
    {
        $this->linkRepository = $linkRepository;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function execute($entity, $arguments = [])
    {
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        if ($entity->getTypeId() != Type::TYPE_DOWNLOADABLE) {
            return $entity;
        }

        /** @var \Magento\Downloadable\Api\Data\LinkInterface[] $links */
        $links = $entity->getExtensionAttributes()->getDownloadableProductLinks() ?: [];
        $updatedLinks = [];
        $oldLinks = $this->linkRepository->getList($entity->getSku());
        foreach ($links as $link) {
            if ($link->getId()) {
                $updatedLinks[$link->getId()] = true;
            }
            $this->linkRepository->save($entity->getSku(), $link, !(bool)$entity->getStoreId());
        }
        /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
        foreach ($oldLinks as $link) {
            if (!isset($updatedLinks[$link->getId()])) {
                $this->linkRepository->delete($link->getId());
            }
        }

        return $entity;
    }
}
