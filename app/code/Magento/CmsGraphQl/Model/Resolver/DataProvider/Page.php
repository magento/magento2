<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\DataProvider;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * Cms page data provider
 */
class Page
{
    /**
     * @var GetPageByIdentifierInterface
     */
    private $pageByIdentifier;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var FilterEmulate
     */
    private $widgetFilter;

    /**
     * @param PageRepositoryInterface $pageRepository
     * @param FilterEmulate $widgetFilter
     * @param GetPageByIdentifierInterface $getPageByIdentifier
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        FilterEmulate $widgetFilter,
        GetPageByIdentifierInterface $getPageByIdentifier
    ) {

        $this->pageRepository = $pageRepository;
        $this->widgetFilter = $widgetFilter;
        $this->pageByIdentifier = $getPageByIdentifier;
    }

    /**
     * Returns page data by page_id
     *
     * @param int $pageId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataByPageId(int $pageId): array
    {
        $page = $this->pageRepository->getById($pageId);

        return $this->convertPageData($page);
    }

    /**
     * Returns page data by page identifier
     *
     * @param string $pageIdentifier
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataByPageIdentifier(string $pageIdentifier, int $storeId): array
    {
        $page = $this->pageByIdentifier->execute($pageIdentifier, $storeId);

        return $this->convertPageData($page);
    }

    /**
     * Convert page data
     *
     * @param PageInterface $page
     * @return array
     * @throws NoSuchEntityException
     */
    private function convertPageData(PageInterface $page)
    {
        if (false === $page->isActive()) {
            throw new NoSuchEntityException();
        }

        $renderedContent = $this->widgetFilter->filter($page->getContent());

        $pageData = [
            'url_key' => $page->getIdentifier(),
            PageInterface::TITLE => $page->getTitle(),
            PageInterface::CONTENT => $renderedContent,
            PageInterface::CONTENT_HEADING => $page->getContentHeading(),
            PageInterface::PAGE_LAYOUT => $page->getPageLayout(),
            PageInterface::META_TITLE => $page->getMetaTitle(),
            PageInterface::META_DESCRIPTION => $page->getMetaDescription(),
            PageInterface::META_KEYWORDS => $page->getMetaKeywords(),
            PageInterface::PAGE_ID => $page->getId(),
            PageInterface::IDENTIFIER => $page->getIdentifier(),
        ];
        return $pageData;
    }
}
