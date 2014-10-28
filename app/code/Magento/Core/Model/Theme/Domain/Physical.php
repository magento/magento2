<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Physical theme model class
 */
namespace Magento\Core\Model\Theme\Domain;

class Physical implements \Magento\Framework\View\Design\Theme\Domain\PhysicalInterface
{
    /**
     * Physical theme model instance
     *
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    protected $_theme;

    /**
     * @var \Magento\Core\Model\ThemeFactory
     */
    protected $_themeFactory;

    /**
     * @var \Magento\Theme\Model\CopyService
     */
    protected $_themeCopyService;

    /**
     * @var \Magento\Core\Model\Resource\Theme\Collection
     */
    protected $_themeCollection;

    /**
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param \Magento\Core\Model\ThemeFactory $themeFactory
     * @param \Magento\Theme\Model\CopyService $themeCopyService
     * @param \Magento\Core\Model\Resource\Theme\Collection $themeCollection
     */
    public function __construct(
        \Magento\Framework\View\Design\ThemeInterface $theme,
        \Magento\Core\Model\ThemeFactory $themeFactory,
        \Magento\Theme\Model\CopyService $themeCopyService,
        \Magento\Core\Model\Resource\Theme\Collection $themeCollection
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
