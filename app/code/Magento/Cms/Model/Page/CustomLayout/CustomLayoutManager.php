<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Page\CustomLayout;

use Magento\Cms\Model\Page\CustomLayout\Data\CustomLayoutSelectedInterface;
use Magento\Cms\Model\Page\CustomLayoutManagerInterface;
use Magento\Cms\Model\ResourceModel\Page;
use Magento\Cms\Model\Page as PageModel;
use Magento\Cms\Model\PageFactory as PageModelFactory;
use Magento\Cms\Model\Page\IdentityMap;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\CollectorInterface;

/**
 * @inheritDoc
 */
class CustomLayoutManager implements CustomLayoutManagerInterface
{
    /**
     * @var Page
     */
    private $pageRepository;

    /**
     * @var PageModelFactory;
     */
    private $pageFactory;

    /**
     * @var IdentityMap
     */
    private $identityMap;

    /**
     * @var CollectorInterface
     */
    private $fileCollector;

    /**
     * @var FlyweightFactory
     */
    private $themeFactory;

    /**
     * @var DesignInterface
     */
    private $design;

    /**
     * @param Page $pageRepository
     * @param PageModelFactory $factory
     * @param IdentityMap $identityMap
     * @param CollectorInterface $fileCollector
     * @param FlyweightFactory $themeFactory
     * @param DesignInterface $design
     */
    public function __construct(
        Page $pageRepository,
        PageModelFactory $factory,
        IdentityMap $identityMap,
        CollectorInterface $fileCollector,
        FlyweightFactory $themeFactory,
        DesignInterface $design
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageFactory = $factory;
        $this->identityMap = $identityMap;
        $this->fileCollector = $fileCollector;
        $this->themeFactory = $themeFactory;
        $this->design = $design;
    }

    /**
     * Find page model by ID.
     *
     * @param int $id
     * @return PageModel
     * @throws NoSuchEntityException
     */
    private function findPage(int $id): PageModel
    {
        if (!$page = $this->identityMap->get($id)) {
            /** @var PageModel $page */
            $this->pageRepository->load($page = $this->pageFactory->create(), $id);
            if (!$page->getIdentifier()) {
                throw NoSuchEntityException::singleField('id', $id);
            }
        }

        return $page;
    }

    /**
     * Adopt page's identifier to be used as layout handle.
     *
     * @param PageModel $page
     * @return string
     */
    private function sanitizeIdentifier(PageModel $page): string
    {
        return str_replace('/', '_', $page->getIdentifier());
    }

    /**
     * Save new custom layout file value for a page.
     *
     * @param int $pageId
     * @param string|null $layoutFile
     * @throws LocalizedException
     * @throws \InvalidArgumentException When invalid file was selected.
     * @throws NoSuchEntityException
     */
    private function saveLayout(int $pageId, ?string $layoutFile): void
    {
        $page = $this->findPage($pageId);
        if ($layoutFile !== null && !in_array($layoutFile, $this->fetchAvailableFiles($pageId), true)) {
            throw new \InvalidArgumentException(
                $layoutFile .' is not available for page #' .$pageId
            );
        }

        $page->setData('layout_update_selected', $layoutFile);
        $this->pageRepository->save($page);
    }

    /**
     * @inheritDoc
     */
    public function save(CustomLayoutSelectedInterface $layout): void
    {
        $this->saveLayout($layout->getPageId(), $layout->getLayoutFileId());
    }

    /**
     * @inheritDoc
     */
    public function deleteFor(int $pageId): void
    {
        $this->saveLayout($pageId, null);
    }

    /**
     * @inheritDoc
     */
    public function fetchAvailableFiles(int $pageId): array
    {
        $page = $this->findPage($pageId);
        $identifier = $this->sanitizeIdentifier($page);
        $layoutFiles = $this->fileCollector->getFiles(
            $this->themeFactory->create($this->design->getConfigurationDesignTheme(Area::AREA_FRONTEND)),
            'cms_page_view_selectable_' .$identifier .'_*.xml'
        );

        return array_filter(
            array_map(
                function (File $file) use ($identifier) : ?string
                {
                    preg_match(
                        '/selectable\_' .preg_quote($identifier) .'\_([a-z0-9]+)/i',
                        $file->getName(),
                        $selectable
                    );
                    if (!empty($selectable[1])) {
                        return $selectable[1];
                    }

                    return null;
                },
                $layoutFiles
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function fetchHandle(int $pageId): ?array
    {
        $page = $this->findPage($pageId);

        return $page['layout_update_selected']
            ? ['selectable' => $this->sanitizeIdentifier($page) .'_' .$page['layout_update_selected']] : null;
    }
}
