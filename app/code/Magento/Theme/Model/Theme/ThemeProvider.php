<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\App\DeploymentConfig;

/**
 * Provide data for theme grid and for theme edit page
 */
class ThemeProvider implements \Magento\Framework\View\Design\Theme\ThemeProviderInterface
{
    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Theme\Model\ThemeFactory
     */
    protected $themeFactory;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface[]
     */
    private $themes;

    /**
     * @var ListInterface
     */
    private $themeList;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * ThemeProvider constructor.
     *
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionFactory
     * @param \Magento\Theme\Model\ThemeFactory $themeFactory
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionFactory,
        \Magento\Theme\Model\ThemeFactory $themeFactory,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->themeFactory = $themeFactory;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getThemeByFullPath($fullPath)
    {
        if (isset($this->themes[$fullPath])) {
            return $this->themes[$fullPath];
        }

        if (! $this->getDeploymentConfig()->isDbAvailable()) {
            return $this->getThemeList()->getThemeByFullPath($fullPath);
        }

        /** @var $themeCollection \Magento\Theme\Model\ResourceModel\Theme\Collection */
        $theme = $this->loadThemeFromCache('theme'. $fullPath);
        if ($theme) {
            $this->themes[$fullPath] = $theme;
            return $theme;
        }
        $themeCollection = $this->collectionFactory->create();
        $theme = $themeCollection->getThemeByFullPath($fullPath);
        if ($theme->getId()) {
            $this->saveThemeToCache($theme, 'theme' . $fullPath);
            $this->saveThemeToCache($theme, 'theme-by-id-' . $theme->getId());
            $this->themes[$fullPath] = $theme;
        }

        return $theme;
    }

    /**
     * {@inheritdoc}
     */
    public function getThemeCustomizations(
        $area = \Magento\Framework\App\Area::AREA_FRONTEND,
        $type = \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
    ) {
        /** @var $themeCollection \Magento\Theme\Model\ResourceModel\Theme\Collection */
        $themeCollection = $this->collectionFactory->create();
        $themeCollection->addAreaFilter($area)->addTypeFilter($type);
        return $themeCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getThemeById($themeId)
    {
        if (isset($this->themes[$themeId])) {
            return $this->themes[$themeId];
        }
        $theme = $this->loadThemeFromCache('theme-by-id-' . $themeId);
        if ($theme) {
            $this->themes[$themeId] = $theme;
            return $theme;
        }
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = $this->themeFactory->create();
        $theme->load($themeId);
        if ($theme->getId()) {
            $this->saveThemeToCache($theme, 'theme-by-id-' . $themeId);
            $this->themes[$themeId] = $theme;
        }
        return $theme;
    }

    /**
     * @param $cacheId
     * @return \Magento\Framework\View\Design\ThemeInterface|null
     */
    private function loadThemeFromCache($cacheId)
    {
        $themeData = $this->cache->load($cacheId);
        if ($themeData) {
            $themeData = json_decode($themeData, true);
            /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
            $theme = $this->themeFactory->create()->populateFromArray($themeData);
            return $theme;
        }

        return null;
    }

    /**
     * @param \Magento\Theme\Model\Theme $theme
     * @param $cacheId
     */
    private function saveThemeToCache(\Magento\Theme\Model\Theme $theme, $cacheId)
    {
        $themeData = json_encode($theme->toArray());
        $this->cache->save($themeData, $cacheId);
    }

    /**
     * @deprecated
     * @return ListInterface
     */
    private function getThemeList()
    {
        if ($this->themeList === null) {
            $this->themeList = ObjectManager::getInstance()->get(ListInterface::class);
        }
        return $this->themeList;
    }

    /**
     * @deprecated
     * @return DeploymentConfig
     */
    private function getDeploymentConfig()
    {
        if ($this->deploymentConfig === null) {
            $this->deploymentConfig = ObjectManager::getInstance()->get(DeploymentConfig::class);
        }
        return $this->deploymentConfig;
    }
}
