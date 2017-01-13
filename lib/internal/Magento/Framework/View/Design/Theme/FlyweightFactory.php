<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Theme factory
 */
class FlyweightFactory
{
    /**
     * Theme provider
     *
     * @var ThemeProviderInterface
     */
    protected $themeProvider;

    /**
     * Themes
     *
     * @var \Magento\Framework\View\Design\ThemeInterface[]
     */
    protected $themes = [];

    /**
     * Themes by path
     *
     * @var \Magento\Framework\View\Design\ThemeInterface[]
     */
    protected $themesByPath = [];

    /**
     * Constructor
     *
     * @param ThemeProviderInterface $themeProvider
     */
    public function __construct(ThemeProviderInterface $themeProvider)
    {
        $this->themeProvider = $themeProvider;
    }

    /**
     * Creates or returns a shared model of theme
     *
     * @param string $themeKey
     * @param string $area
     * @return \Magento\Framework\View\Design\ThemeInterface|null
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function create($themeKey, $area = \Magento\Framework\View\DesignInterface::DEFAULT_AREA)
    {
        if (!is_numeric($themeKey) && !is_string($themeKey)) {
            throw new \InvalidArgumentException('Incorrect theme identification key');
        }
        $themeKey = $this->extractThemeId($themeKey);
        if (is_numeric($themeKey)) {
            $themeModel = $this->_loadById($themeKey);
        } else {
            $themeModel = $this->_loadByPath($themeKey, $area);
        }
        if (!$themeModel->getId()) {
            throw new \LogicException("Unable to load theme by specified key: '{$themeKey}'");
        }
        $this->_addTheme($themeModel);
        return $themeModel;
    }

    /**
     * Attempt to determine a numeric theme ID from the specified path
     *
     * @param string $path
     * @return string
     */
    private function extractThemeId($path)
    {
        $dir = \Magento\Framework\View\DesignInterface::PUBLIC_THEME_DIR;
        if (preg_match('/^' . preg_quote($dir, '/') . '(\d+)$/', $path, $matches)) {
            return $matches[1];
        }
        return $path;
    }

    /**
     * Load theme by id
     *
     * @param int $themeId
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    protected function _loadById($themeId)
    {
        if (isset($this->themes[$themeId])) {
            return $this->themes[$themeId];
        }

        return $this->themeProvider->getThemeById($themeId);
    }

    /**
     * Load theme by theme path
     *
     * @param string $themePath
     * @param string $area
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    protected function _loadByPath($themePath, $area)
    {
        $fullPath = $area . \Magento\Framework\View\Design\ThemeInterface::PATH_SEPARATOR . $themePath;
        if (isset($this->themesByPath[$fullPath])) {
            return $this->themesByPath[$fullPath];
        }

        return $this->themeProvider->getThemeByFullPath($fullPath);
    }

    /**
     * Add theme to shared collection
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $themeModel
     * @return $this
     */
    protected function _addTheme(\Magento\Framework\View\Design\ThemeInterface $themeModel)
    {
        if ($themeModel->getId()) {
            $this->themes[$themeModel->getId()] = $themeModel;
            $themePath = $themeModel->getFullPath();
            if ($themePath) {
                $this->themesByPath[$themePath] = $themeModel;
            }
        }
        return $this;
    }
}
