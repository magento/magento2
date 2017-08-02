<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Link;

use Magento\Downloadable\Api\LinkRepositoryInterface as LinkRepository;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @since 2.1.0
 */
class ReadHandler implements ExtensionInterface
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
