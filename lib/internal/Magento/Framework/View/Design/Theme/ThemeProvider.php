<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

use Magento\Framework\View\Design\ThemeFactory;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Provide data for theme grid and for theme edit page
 * @since 2.2.0
 */
class ThemeProvider implements \Magento\Framework\View\Design\Theme\ThemeProviderInterface
{
    /**
     * @var ListInterface
     * @since 2.2.0
     */
    private $themeList;

    /**
     * @var ThemeFactory
     * @since 2.2.0
     */
    protected $themeFactory;

    /**
     * @var ThemeInterface[]
     * @since 2.2.0
     */
    private $themes;

    /**
     * ThemeProvider constructor
     *
     * @param ListInterface $themeList
     * @param ThemeFactory  $themeFactory
     * @since 2.2.0
     */
    public function __construct(
        ListInterface $themeList,
        ThemeFactory $themeFactory
    ) {
        $this->themeList = $themeList;
        $this->themeFactory = $themeFactory;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getThemeByFullPath($fullPath)
    {
        if (!isset($this->themes[$fullPath])) {
            $this->themes[$fullPath] = $this->themeList->getThemeByFullPath($fullPath);
        }
        return $this->themes[$fullPath];
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function getThemeCustomizations(
        $area = \Magento\Framework\App\Area::AREA_FRONTEND,
        $type = \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
    ) {
        return [];
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getThemeById($themeId)
    {
        return $this->themeFactory->getTheme($themeId);
    }
}
