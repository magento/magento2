<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\Cms;

use Magento\Cms\Api\BlockRepositoryInterface as CmsBlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface as CmsBlockInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Cms block field data provider, used for GraphQL request processing.
 */
class CmsBlockDataProvider
{
    /**
     * @var CmsBlockRepositoryInterface
     */
    private $cmsBlockRepository;

    /**
     * @param CmsBlockRepositoryInterface $cmsBlockRepository
     */
    public function __construct(
        CmsBlockRepositoryInterface $cmsBlockRepository
    ) {
        $this->cmsBlockRepository = $cmsBlockRepository;
    }

    /**
     * Get CMS block data by identifier
     *
     * @param string $cmsBlockIdentifier
     * @return array|GraphQlInputException
     * @throws NoSuchEntityException
     */
    public function getCmsBlockById(string $cmsBlockIdentifier)
    {
        $cmsBlockModel = $this->cmsBlockRepository->getById($cmsBlockIdentifier);

        if (!$cmsBlockModel->isActive()) {
            throw new NoSuchEntityException();
        }

        return $this->processCmsBlock($cmsBlockModel);
    }

    /**
     * Transform single CMS block data from object to in array format
     *
     * @param CmsBlockInterface $cmsBlockModel
     * @return array
     */
    private function processCmsBlock(CmsBlockInterface $cmsBlockModel) : array
    {
        $cmsBlockData = [
            'identifier' => $cmsBlockModel->getIdentifier(),
            'title' => $cmsBlockModel->getTitle(),
            'content' => $cmsBlockModel->getContent(),
        ];

        return $cmsBlockData;
    }

}