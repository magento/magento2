<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Get CMS page data by Id
 */
class PageDataProvider
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(
        PageRepositoryInterface $pageRepository
    ) {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @param int $pageId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(int $pageId) : array
    {
        $page = $this->pageRepository->getById($pageId);

        if (false === $page->isActive()) {
            throw new NoSuchEntityException();
        }

        $pageData = [
            'url_key' => $page->getIdentifier(),
            PageInterface::TITLE => $page->getTitle(),
            PageInterface::CONTENT => $page->getContent(),
            PageInterface::CONTENT_HEADING => $page->getContentHeading(),
            PageInterface::PAGE_LAYOUT => $page->getPageLayout(),
            PageInterface::META_TITLE => $page->getMetaTitle(),
            PageInterface::META_DESCRIPTION => $page->getMetaDescription(),
            PageInterface::META_KEYWORDS => $page->getMetaKeywords(),
        ];
        return $pageData;
    }
}
