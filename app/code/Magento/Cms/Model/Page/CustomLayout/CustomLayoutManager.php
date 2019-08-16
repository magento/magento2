<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Page\CustomLayout;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page\CustomLayout\Data\CustomLayoutSelectedInterface;
use Magento\Cms\Model\Page\CustomLayoutManagerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\Result\Page as PageLayout;

/**
 * @inheritDoc
 */
class CustomLayoutManager implements CustomLayoutManagerInterface
{
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
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @param CollectorInterface $fileCollector
     * @param FlyweightFactory $themeFactory
     * @param DesignInterface $design
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(
        CollectorInterface $fileCollector,
        FlyweightFactory $themeFactory,
        DesignInterface $design,
        PageRepositoryInterface $pageRepository
    ) {
        $this->fileCollector = $fileCollector;
        $this->themeFactory = $themeFactory;
        $this->design = $design;
        $this->pageRepository = $pageRepository;
    }

    /**
     * Adopt page's identifier to be used as layout handle.
     *
     * @param PageInterface $page
     * @return string
     */
    private function sanitizeIdentifier(PageInterface $page): string
    {
        return str_replace('/', '_', $page->getIdentifier());
    }

    /**
     * @inheritDoc
     */
    public function fetchAvailableFiles(PageInterface $page): array
    {
        $identifier = $this->sanitizeIdentifier($page);
        $layoutFiles = $this->fileCollector->getFiles(
            $this->themeFactory->create($this->design->getConfigurationDesignTheme(Area::AREA_FRONTEND)),
            'cms_page_view_selectable_' .$identifier .'_*.xml'
        );

        return array_filter(
            array_map(
                function (File $file) use ($identifier) : ?string {
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
    public function applyUpdate(PageLayout $layout, CustomLayoutSelectedInterface $layoutSelected): void
    {
        $page = $this->pageRepository->getById($layoutSelected->getPageId());

        $layout->addPageLayoutHandles(
            ['selectable' => $this->sanitizeIdentifier($page) .'_' .$layoutSelected->getLayoutFileId()]
        );
    }
}
