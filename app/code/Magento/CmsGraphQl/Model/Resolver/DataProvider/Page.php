<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\DataProvider;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * Cms page data provider
 */
class Page
{
    /**
     * @var FilterEmulate
     */
    private $widgetFilter;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @param PageRepositoryInterface $pageRepository
     * @param FilterEmulate $widgetFilter
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        FilterEmulate $widgetFilter
    ) {
        $this->pageRepository = $pageRepository;
        $this->widgetFilter = $widgetFilter;
    }

    /**
     * Get the page data
     *
     * @param int $pageId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(int $pageId): array
    {
        $page = $this->pageRepository->getById($pageId);

        if (false === $page->isActive()) {
            throw new NoSuchEntityException();
        }

        $renderedContent = $this->widgetFilter->filter($page->getContent());

        $pageData = [
            PageInterface::PAGE_ID => $page->getId(),
            'url_key' => $page->getIdentifier(),
            PageInterface::TITLE => $page->getTitle(),
            PageInterface::CONTENT => $renderedContent,
            PageInterface::CONTENT_HEADING => $page->getContentHeading(),
            PageInterface::PAGE_LAYOUT => $page->getPageLayout(),
            PageInterface::META_TITLE => $page->getMetaTitle(),
            PageInterface::META_DESCRIPTION => $page->getMetaDescription(),
            PageInterface::META_KEYWORDS => $page->getMetaKeywords(),
        ];
        return $pageData;
    }
}
