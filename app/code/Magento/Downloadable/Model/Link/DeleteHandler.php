<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Link;

use Magento\Downloadable\Api\LinkRepositoryInterface as LinkRepository;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class DeleteHandler
 * @since 2.1.0
 */
class DeleteHandler implements ExtensionInterface
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
        if ($entity->getTypeId() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $entity;
        }
        /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
        foreach ($this->linkRepository->getList($entity->getSku()) as $link) {
            $this->linkRepository->delete($link->getId());
        }
        return $entity;
    }
}
