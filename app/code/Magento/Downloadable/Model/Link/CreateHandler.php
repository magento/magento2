<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Link;

use Magento\Downloadable\Api\LinkRepositoryInterface as LinkRepository;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class CreateHandler
 */
class CreateHandler implements ExtensionInterface
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
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        if ($entity->getTypeId() != Type::TYPE_DOWNLOADABLE) {
            return $entity;
        }

        /** @var \Magento\Downloadable\Api\Data\LinkInterface[] $links */
        $links = $entity->getExtensionAttributes()->getDownloadableProductLinks() ?: [];
        foreach ($links as $link) {
            $link->setId(null);
            $this->linkRepository->save($entity->getSku(), $link, !(bool)$entity->getStoreId());
        }
        
        return $entity;
    }
}
