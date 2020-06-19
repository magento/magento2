<?php
/**
 * Magento validator config factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\PageLayout\Config;

/**
 * Page layout config builder
 */
class Builder implements \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
{
    const CACHE_KEY_LAYOUTS = 'THEME_LAYOUTS_FILES_MERGED';

    /**
     * @var \Magento\Framework\View\PageLayout\ConfigFactory
     */
    protected $configFactory;

    /**
     * @var \Magento\Framework\View\PageLayout\File\Collector\Aggregated
     */
    protected $fileCollector;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\Collection
     */
    protected $themeCollection;

    /**
     * @var array
     */
    private $configFiles = [];

    /**
     * @var \Magento\Framework\App\Cache\Type\Layout
     */
    protected $cacheModel;

    /**
     * @param \Magento\Framework\View\PageLayout\ConfigFactory $configFactory
     * @param \Magento\Framework\View\PageLayout\File\Collector\Aggregated $fileCollector
     * @param \Magento\Theme\Model\ResourceModel\Theme\Collection $themeCollection
     * @param \Magento\Framework\App\Cache\Type\Layout $cacheModel
     */
    public function __construct(
        \Magento\Framework\View\PageLayout\ConfigFactory $configFactory,
        \Magento\Framework\View\PageLayout\File\Collector\Aggregated $fileCollector,
        \Magento\Theme\Model\ResourceModel\Theme\Collection $themeCollection,
        \Magento\Framework\App\Cache\Type\Layout $cacheModel
    ) {
        $this->configFactory = $configFactory;
        $this->fileCollector = $fileCollector;
        $this->themeCollection = $themeCollection;
        $this->cacheModel = $cacheModel;
        $this->themeCollection->setItemObjectClass(\Magento\Theme\Model\Theme\Data::class);
    }

    /**
     * @inheritdoc
     */
    public function getPageLayoutsConfig()
    {
        return $this->configFactory->create(['configFiles' => $this->getConfigFiles()]);
    }

    /**
     * Retrieve configuration files. Caches merged layouts.xml XML files.
     *
     * @return array
     */
    protected function getConfigFiles()
    {
        if (!$this->configFiles) {
            $configFiles = [];
            $this->configFiles = $this->cacheModel->load(self::CACHE_KEY_LAYOUTS);
            if (!empty($this->configFiles)) {
                $this->configFiles = @unserialize($this->configFiles);//if value in cache is corrupted.
            }
            if (empty($this->configFiles)) {
                foreach ($this->themeCollection->loadRegisteredThemes() as $theme) {
                    $configFiles[] = $this->fileCollector->getFilesContent($theme, 'layouts.xml');
                }
                $this->configFiles = array_merge(...$configFiles);
                $this->cacheModel->save(serialize($this->configFiles), self::CACHE_KEY_LAYOUTS);
            }
        }

        return $this->configFiles;
    }
}
