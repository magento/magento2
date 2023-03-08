<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Physical theme model class
 */
namespace Magento\Theme\Model\Theme\Domain;

use Magento\Framework\App\Area;
use Magento\Framework\View\Design\Theme\Domain\PhysicalInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\CopyService;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Theme\Model\ThemeFactory;

class Physical implements PhysicalInterface
{
    /**
     * Physical theme model instance
     *
     * @var ThemeInterface
     */
    protected $_theme;

    /**
     * @var ThemeFactory
     */
    protected $_themeFactory;

    /**
     * @var CopyService
     */
    protected $_themeCopyService;

    /**
     * @var ThemeCollection
     */
    protected $_themeCollection;

    /**
     * @param ThemeInterface $theme
     * @param ThemeFactory $themeFactory
     * @param CopyService $themeCopyService
     * @param ThemeCollection $themeCollection
     */
    public function __construct(
        ThemeInterface $theme,
        ThemeFactory $themeFactory,
        CopyService $themeCopyService,
        ThemeCollection $themeCollection
    ) {
        $this->_theme = $theme;
        $this->_themeFactory = $themeFactory;
        $this->_themeCopyService = $themeCopyService;
        $this->_themeCollection = $themeCollection;
    }

    /**
     * Create theme customization
     *
     * @param ThemeInterface $theme
     * @return ThemeInterface
     */
    public function createVirtualTheme($theme)
    {
        $themeData = $theme->getData();
        $themeData['parent_id'] = $theme->getId();
        $themeData['theme_id'] = null;
        $themeData['theme_path'] = null;
        $themeData['theme_title'] = $this->_getVirtualThemeTitle($theme);
        $themeData['type'] = ThemeInterface::TYPE_VIRTUAL;

        /** @var ThemeInterface $themeCustomization */
        $themeCustomization = $this->_themeFactory->create()->setData($themeData);
        $themeCustomization->getThemeImage()->createPreviewImageCopy($theme);
        $themeCustomization->save();

        $this->_themeCopyService->copy($theme, $themeCustomization);

        return $themeCustomization;
    }

    /**
     * Get virtual theme title
     *
     * @param ThemeInterface $theme
     * @return string
     */
    protected function _getVirtualThemeTitle($theme)
    {
        $themeCopyCount = $this->_themeCollection->addAreaFilter(
            Area::AREA_FRONTEND
        )->addTypeFilter(
            ThemeInterface::TYPE_VIRTUAL
        )->addFilter(
            'parent_id',
            $theme->getId()
        )->count();

        $title = sprintf("%s - %s #%s", $theme->getThemeTitle(), __('Copy'), $themeCopyCount + 1);
        return $title;
    }
}
