<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\Cms;

use Magento\Cms\Api\Data\PageInterface as CmsPageInterface;
use Magento\Cms\Api\PageRepositoryInterface as CmsPageRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Cms field data provider, used for GraphQL request processing.
 */
class CmsPageDataProvider
{
    /**
     * @var CmsPageRepositoryInterface
     */
    private $cmsPageRepository;

    /**
     * @param CmsPageRepositoryInterface $cmsPageRepository
     */
    public function __construct(
        CmsPageRepositoryInterface $cmsPageRepository
    ) {
        $this->cmsPageRepository = $cmsPageRepository;
    }

    /**
     * Get CMS page data by Id
     *
     * @param int $cmsPageId
     * @return array
     * @throws LocalizedException
     */
    public function getCmsPageById(int $cmsPageId) : array
    {
        try {
            $cmsPageModel = $this->cmsPageRepository->getById($cmsPageId);

            if (!$cmsPageModel->isActive()) {
                throw new NoSuchEntityException();
            }

        } catch (NoSuchEntityException $e) {
            // No error should be thrown, null result should be returned
            return [];
        }

        return $this->processCmsPage($cmsPageModel);
    }

    /**
     * Transform single CMS page data from object to in array format
     *
     * @param CmsPageInterface $cmsPageModel
     * @return array
     */
    private function processCmsPage(CmsPageInterface $cmsPageModel) : array
    {
        $cmsPageData = [
            'url_key' => $cmsPageModel->getIdentifier(),
            'page_title' => $cmsPageModel->getTitle(),
            'page_content' => $cmsPageModel->getContent(),
            'content_heading' => $cmsPageModel->getContentHeading(),
            'layout' => $cmsPageModel->getPageLayout(),
            'mate_title' => $cmsPageModel->getMetaTitle(),
            'meta_description' => $cmsPageModel->getMetaDescription(),
            'meta_keywords' => $cmsPageModel->getMetaKeywords(),
        ];

        return $cmsPageData;
    }
}
