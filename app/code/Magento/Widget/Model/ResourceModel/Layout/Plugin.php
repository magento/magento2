<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Widget\Model\ResourceModel\Layout;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\PageLayout\ConfigFactory as PageLayoutConfigFactory;
use Magento\Framework\View\PageLayout\File\Collector\Aggregated as PageLayoutFileCollector;

class Plugin
{
    /**
     * @var Update
     */
    private $update;

    /**
     * @var PageLayoutConfigFactory
     */
    private $pageLayoutConfigFactory;

    /**
     * @var PageLayoutFileCollector
     */
    private $pageLayoutFileCollector;

    /**
     * @var array
     */
    private $pageLayoutsByTheme = [];

    /**
     * @var \WeakMap
     */
    private $pageLayoutMerges;

    /**
     * @param Update $update
     * @param PageLayoutConfigFactory|null $pageLayoutConfigFactory
     * @param PageLayoutFileCollector|null $pageLayoutFileCollector
     */
    public function __construct(
        Update $update,
        ?PageLayoutConfigFactory $pageLayoutConfigFactory = null,
        ?PageLayoutFileCollector $pageLayoutFileCollector = null
    ) {
        $this->update = $update;
        $this->pageLayoutConfigFactory = $pageLayoutConfigFactory
            ?? ObjectManager::getInstance()->get(PageLayoutConfigFactory::class);
        $this->pageLayoutFileCollector = $pageLayoutFileCollector
            ?? ObjectManager::getInstance()->get(PageLayoutFileCollector::class);
        $this->pageLayoutMerges = new \WeakMap();
    }

    /**
     * Around update
     *
     * @param \Magento\Framework\View\Model\Layout\Merge $subject
     * @param callable $proceed
     * @param string $handle
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetDbUpdateString(
        \Magento\Framework\View\Model\Layout\Merge $subject,
        \Closure $proceed,
        $handle
    ) {
        if ($this->isInheritedPageLayoutHandle($subject, (string)$handle)) {
            return '';
        }
        return $this->update->fetchUpdatesByHandle($handle, $subject->getTheme(), $subject->getScope());
    }

    /**
     * Whether the handle is a page layout reached through another page layout's <update handle="..."/>
     *
     * Updates assigned to a page layout apply only to pages using exactly that layout, not to the layouts built on it.
     *
     * @param \Magento\Framework\View\Model\Layout\Merge $subject
     * @param string $handle
     * @return bool
     */
    private function isInheritedPageLayoutHandle(
        \Magento\Framework\View\Model\Layout\Merge $subject,
        string $handle
    ): bool {
        if (in_array($handle, array_map('strval', $subject->getHandles()), true)) {
            return false;
        }
        $theme = $subject->getTheme();
        return $theme instanceof ThemeInterface
            && $this->isPageLayoutMerge($subject)
            && in_array($handle, $this->getPageLayouts($theme), true);
    }

    /**
     * Whether the merge is built from page layout files only, as the one used by the page layout reader
     *
     * Layout files with a <page> root become <handle> nodes; the page layout reader merges page_layout files
     * only, all with a <layout> root, so its merge is the one without <handle> nodes.
     *
     * @param \Magento\Framework\View\Model\Layout\Merge $subject
     * @return bool
     */
    private function isPageLayoutMerge(\Magento\Framework\View\Model\Layout\Merge $subject): bool
    {
        if (!isset($this->pageLayoutMerges[$subject])) {
            $this->pageLayoutMerges[$subject] = !$subject->getFileLayoutUpdatesXml()->xpath('handle[1]');
        }
        return $this->pageLayoutMerges[$subject];
    }

    /**
     * Page layout ids declared in the theme's layouts.xml
     *
     * @param ThemeInterface $theme
     * @return string[]
     */
    private function getPageLayouts(ThemeInterface $theme): array
    {
        $themeKey = $theme->getId() . '|' . $theme->getFullPath();
        if (!isset($this->pageLayoutsByTheme[$themeKey])) {
            $configFiles = $this->pageLayoutFileCollector->getFilesContent($theme, 'layouts.xml');
            $pageLayouts = $configFiles
                ? $this->pageLayoutConfigFactory->create(['configFiles' => $configFiles])->getPageLayouts()
                : [];
            $this->pageLayoutsByTheme[$themeKey] = array_map('strval', array_keys($pageLayouts));
        }
        return $this->pageLayoutsByTheme[$themeKey];
    }
}
