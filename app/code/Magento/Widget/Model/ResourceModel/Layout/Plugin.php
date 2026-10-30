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
     * @var PageLayoutReaderPlugin
     */
    private $pageLayoutReaderPlugin;

    /**
     * @param Update $update
     * @param PageLayoutConfigFactory|null $pageLayoutConfigFactory
     * @param PageLayoutFileCollector|null $pageLayoutFileCollector
     * @param PageLayoutReaderPlugin|null $pageLayoutReaderPlugin
     */
    public function __construct(
        Update $update,
        ?PageLayoutConfigFactory $pageLayoutConfigFactory = null,
        ?PageLayoutFileCollector $pageLayoutFileCollector = null,
        ?PageLayoutReaderPlugin $pageLayoutReaderPlugin = null
    ) {
        $this->update = $update;
        $this->pageLayoutConfigFactory = $pageLayoutConfigFactory
            ?? ObjectManager::getInstance()->get(PageLayoutConfigFactory::class);
        $this->pageLayoutFileCollector = $pageLayoutFileCollector
            ?? ObjectManager::getInstance()->get(PageLayoutFileCollector::class);
        $this->pageLayoutReaderPlugin = $pageLayoutReaderPlugin
            ?? ObjectManager::getInstance()->get(PageLayoutReaderPlugin::class);
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
        if (!$this->pageLayoutReaderPlugin->isReading()
            || in_array($handle, array_map('strval', $subject->getHandles()), true)
        ) {
            return false;
        }
        $theme = $subject->getTheme();
        return $theme instanceof ThemeInterface && in_array($handle, $this->getPageLayouts($theme), true);
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
