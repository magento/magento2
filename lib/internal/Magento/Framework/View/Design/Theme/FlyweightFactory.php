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
     * Tries to find theme in File System by specific path or load theme from DB
     * by specific path (e.g. adminhtml/Magento/backend) or by identifier (theme primary key)
     * Can be used to deploy static or in other setup commands, even if Magento is not installed yet.
     *
     * @param string $themeKey - Should looks like Magento/backend or should be theme primary key
     * @param string $area - Can be adminhtml, frontend, etc...
     * @return \Magento\Framework\View\Design\ThemeInterface
     * @throws \InvalidArgumentException when incorrect themeKey was specified
     * @throws \LogicException when theme with appropriate themeKey was not found
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
        if (!$themeModel->getCode()) {
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
