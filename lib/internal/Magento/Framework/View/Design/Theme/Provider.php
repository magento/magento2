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

namespace Magento\Framework\View\Design\Theme;

class Provider
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var FlyweightFactory
     */
    private $flyweightFactory;

    /**
     * @var ListInterface
     */
    private $themeList;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param FlyweightFactory $flyweightFactory
     * @param ListInterface $themeList
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        FlyweightFactory $flyweightFactory,
        ListInterface $themeList
    ) {
        $this->appState = $appState;
        $this->flyweightFactory = $flyweightFactory;
        $this->themeList = $themeList;
    }

    /**
     * Get theme model by theme path and area code
     *
     * @param string $themePath
     * @param string $areaCode
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    public function getThemeModel($themePath, $areaCode)
    {
        if ($this->appState->isInstalled()) {
            $themeModel = $this->flyweightFactory->create($themePath, $areaCode);
        } else {
            $themeModel = $this->themeList->getThemeByFullPath($areaCode . '/' . $themePath);
        }
        return $themeModel;
    }
}
