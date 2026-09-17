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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\View\LayoutInterface;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * Cms page data provider
 */
class Page
{
    /**
     * Resolved-data key carrying cache tags of entities rendered by widgets inside the page content.
     */
    public const WIDGET_IDENTITIES = 'widget_identities';

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
     * @var LayoutInterface
     */
    private LayoutInterface $layout;

    /**
     * @param PageRepositoryInterface $pageRepository
     * @param FilterEmulate $widgetFilter
     * @param GetPageByIdentifierInterface $getPageByIdentifier
     * @param LayoutInterface|null $layout
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        FilterEmulate $widgetFilter,
        GetPageByIdentifierInterface $getPageByIdentifier,
        ?LayoutInterface $layout = null
    ) {

        $this->pageRepository = $pageRepository;
        $this->widgetFilter = $widgetFilter;
        $this->pageByIdentifier = $getPageByIdentifier;
        $this->layout = $layout ?? ObjectManager::getInstance()->get(LayoutInterface::class);
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
            PageInterface::IDENTIFIER => $page->getIdentifier(),
            self::WIDGET_IDENTITIES => []
        ];
        if (empty($fields) || in_array(PageInterface::CONTENT, $fields)) {
            $blocksBefore = $this->layout->getAllBlocks();
            $pageData[PageInterface::CONTENT] = $this->widgetFilter->filter($page->getContent());
            $pageData[self::WIDGET_IDENTITIES] = $this->collectWidgetIdentities($blocksBefore);
        }
        return $pageData;
    }

    /**
     * Collect cache tags of the widget blocks rendered while filtering the page content.
     *
     * Mirrors how FPC tags a page (see PageCache\Model\Layout\LayoutPlugin), so disabling/saving a product
     * shown by an embedded widget invalidates the cached GraphQL response as well.
     *
     * @param array $blocksBefore Blocks already in the shared layout before the content was rendered
     * @return string[]
     */
    private function collectWidgetIdentities(array $blocksBefore): array
    {
        $identities = [];
        foreach (array_diff_key($this->layout->getAllBlocks(), $blocksBefore) as $block) {
            if ($block instanceof IdentityInterface) {
                $identities[] = $block->getIdentities();
            }
        }
        return $identities ? array_values(array_unique(array_merge([], ...$identities))) : [];
    }
}
