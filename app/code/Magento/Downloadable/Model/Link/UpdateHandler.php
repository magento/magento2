<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Downloadable\Model\Link;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Downloadable\Api\LinkRepositoryInterface as LinkRepository;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * UpdateHandler for downloadable product links
 */
class UpdateHandler implements ExtensionInterface
{
    private const GLOBAL_SCOPE_ID = 0;

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
     * Update links for downloadable product if exist
     *
     * @param ProductInterface $entity
     * @param array $arguments
     * @return ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = []): ProductInterface
    {
        $links = $entity->getExtensionAttributes()->getDownloadableProductLinks();

        if ($links && $entity->getTypeId() === Type::TYPE_DOWNLOADABLE) {
            $this->updateLinks($entity, $links);
        }

        return $entity;
    }

    /**
     * Update product links
     *
     * @param ProductInterface $entity
     * @param array $links
     * @return void
     */
    private function updateLinks(ProductInterface $entity, array $links): void
    {
        $isGlobalScope = (int) $entity->getStoreId() === self::GLOBAL_SCOPE_ID;
        $oldLinks = $this->linkRepository->getList($entity->getSku());

        $updatedLinks = [];
        foreach ($links as $link) {
            if ($link->getId()) {
                $updatedLinks[$link->getId()] = true;
            }
            $this->linkRepository->save($entity->getSku(), $link, $isGlobalScope);
        }

        foreach ($oldLinks as $link) {
            if (!isset($updatedLinks[$link->getId()])) {
                $this->linkRepository->delete($link->getId());
            }
        }
    }
}
