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
use Magento\Framework\App\ObjectManager;
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
     * @param PageRepositoryInterface $pageRepository
     * @param FilterEmulate $widgetFilter
     * @param GetPageByIdentifierInterface $getPageByIdentifier
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        FilterEmulate $widgetFilter,
        GetPageByIdentifierInterface $getPageByIdentifier = null,
        StoreManagerInterface $storeManager = null
    ) {

        $this->pageRepository = $pageRepository;
        $this->widgetFilter = $widgetFilter;
        $this->pageByIdentifier = $getPageByIdentifier ?: ObjectManager::getInstance()->get(GetPageByIdentifierInterface::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * @deprecated
     * @see getDataByPageId(int $pageId)
     *
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

        return $this->convertPageData($page);
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

        return $this->convertPageData($page, false, true);
    }

    /**
     * Returns page data by page identifier
     *
     * @param string $pageIdentifier
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataByPageIdentifier(string $pageIdentifier): array
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $page = $this->pageByIdentifier->execute($pageIdentifier, $storeId);

        return $this->convertPageData($page, false, true);
    }

    /**
     * @param PageInterface $page
     * @param bool $includePageId
     * @param bool $includePageIdentifier
     * @return array
     * @throws NoSuchEntityException
     */
    private function convertPageData(PageInterface $page, $includePageId = true, $includePageIdentifier = false)
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
        ];

        if ($includePageId) {
            $pageData[PageInterface::PAGE_ID] = $page->getId();
        }

        if ($includePageIdentifier) {
            $pageData[PageInterface::IDENTIFIER] = $page->getIdentifier();
        }

        return $pageData;
    }
}
