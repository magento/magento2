<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\DataProvider;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * Cms page data provider
 */
class Page
{
    /**
     * @var GetPageByIdentifierInterface
     */
    private GetPageByIdentifierInterface $pageByIdentifier;

    /**
     * @var PageRepositoryInterface
     */
    private PageRepositoryInterface $pageRepository;

    /**
     * @var FilterEmulate
     */
    private FilterEmulate $widgetFilter;

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
     * @param ResolveInfo|null $info
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getDataByPageId(int $pageId, ?ResolveInfo $info = null): array
    {
        $page = $this->pageRepository->getById($pageId);
        return $this->convertPageData($page, $info ? array_keys($info->getFieldSelection(1)) : []);
    }

    /**
     * Returns page data by page identifier
     *
     * @param string $pageIdentifier
     * @param int $storeId
     * @param ResolveInfo|null $info
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataByPageIdentifier(string $pageIdentifier, int $storeId, ?ResolveInfo $info = null): array
    {
        $page = $this->pageByIdentifier->execute($pageIdentifier, $storeId);

        return $this->convertPageData($page, $info ? array_keys($info->getFieldSelection(1)) : []);
    }

    /**
     * Convert page data
     *
     * @param PageInterface $page
     * @param array $fields
     * @return array
     * @throws NoSuchEntityException
     */
    private function convertPageData(PageInterface $page, array $fields = []): array
    {
        if (false === $page->isActive()) {
            throw new NoSuchEntityException();
        }

        $pageData = [
            'url_key' => $page->getIdentifier(),
            PageInterface::TITLE => $page->getTitle(),
            PageInterface::CONTENT_HEADING => $page->getContentHeading(),
            PageInterface::PAGE_LAYOUT => $page->getPageLayout(),
            PageInterface::META_TITLE => $page->getMetaTitle(),
            PageInterface::META_DESCRIPTION => $page->getMetaDescription(),
            PageInterface::META_KEYWORDS => $page->getMetaKeywords(),
            PageInterface::PAGE_ID => $page->getId(),
            PageInterface::IDENTIFIER => $page->getIdentifier()
        ];
        if (empty($fields) || in_array(PageInterface::CONTENT, $fields)) {
            $pageData[PageInterface::CONTENT] = $this->widgetFilter->filter($page->getContent());
        }
        return $pageData;
    }
}
