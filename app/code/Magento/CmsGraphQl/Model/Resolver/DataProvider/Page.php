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
use Magento\Store\Model\StoreManagerInterface;
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FilterEmulate
     */
    private $widgetFilter;

    /**
     * @param GetPageByIdentifierInterface $getPageByIdentifier
     * @param FilterEmulate $widgetFilter
     * @param PageRepositoryInterface $pageRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GetPageByIdentifierInterface $getPageByIdentifier,
        FilterEmulate $widgetFilter,
        PageRepositoryInterface $pageRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->pageByIdentifier = $getPageByIdentifier;
        $this->pageRepository = $pageRepository;
        $this->storeManager = $storeManager;
        $this->widgetFilter = $widgetFilter;
    }

    /**
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
     * @param string $pageIdentifier
     * @return array
     */
    public function getDataByPageIdentifier(string $pageIdentifier): array
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $page = $this->pageByIdentifier->execute($pageIdentifier, $storeId);

        return $this->convertPageData($page);
    }

    /**
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
            PageInterface::PAGE_ID => $page->getId(),
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
