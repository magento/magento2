<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Physical theme model class
 */
namespace Magento\Theme\Model\Theme\Domain;

class Physical implements \Magento\Framework\View\Design\Theme\Domain\PhysicalInterface
{
    /**
     * Physical theme model instance
     *
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    protected $_theme;

    /**
     * @var \Magento\Theme\Model\ThemeFactory
     */
    protected $_themeFactory;

    /**
     * @var \Magento\Theme\Model\CopyService
     */
    protected $_themeCopyService;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\Collection
     */
    protected $_themeCollection;

    /**
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param \Magento\Theme\Model\ThemeFactory $themeFactory
     * @param \Magento\Theme\Model\CopyService $themeCopyService
     * @param \Magento\Theme\Model\ResourceModel\Theme\Collection $themeCollection
     */
    public function __construct(
        \Magento\Framework\View\Design\ThemeInterface $theme,
        \Magento\Theme\Model\ThemeFactory $themeFactory,
        \Magento\Theme\Model\CopyService $themeCopyService,
        \Magento\Theme\Model\ResourceModel\Theme\Collection $themeCollection
    ) {
        $this->_theme = $theme;
        $this->_themeFactory = $themeFactory;
        $this->_themeCopyService = $themeCopyService;
        $this->_themeCollection = $themeCollection;
    }

    /**
     * Create theme customization
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    public function createVirtualTheme($theme)
    {
        $themeData = $theme->getData();
        $themeData['parent_id'] = $theme->getId();
        $themeData['theme_id'] = null;
        $themeData['theme_path'] = null;
        $themeData['theme_title'] = $this->_getVirtualThemeTitle($theme);
        $themeData['type'] = \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL;

        /** @var $themeCustomization \Magento\Framework\View\Design\ThemeInterface */
        $themeCustomization = $this->_themeFactory->create()->setData($themeData);
        $themeCustomization->getThemeImage()->createPreviewImageCopy($theme);
        $themeCustomization->save();

        $this->_themeCopyService->copy($theme, $themeCustomization);

        return $themeCustomization;
    }

    /**
     * Get virtual theme title
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return string
     */
    protected function _getVirtualThemeTitle($theme)
    {
        $themeCopyCount = $this->_themeCollection->addAreaFilter(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->addTypeFilter(
            \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
        )->addFilter(
            'parent_id',
            $theme->getId()
        )->count();

        $title = sprintf("%s - %s #%s", $theme->getThemeTitle(), __('Copy'), $themeCopyCount + 1);
        return $title;
    }
}
