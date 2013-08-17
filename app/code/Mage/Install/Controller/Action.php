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
 * @category    Mage
 * @package     Mage_Install
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Install_Controller_Action extends Mage_Core_Controller_Varien_Action
{
    protected function _construct()
    {
        parent::_construct();

        $this->setCurrentArea('install');
        $this->setFlag('', self::FLAG_NO_CHECK_INSTALLATION, true);
    }

    /**
     * Initialize area and design
     *
     * @return Mage_Install_Controller_Action
     */
    protected function _initDesign()
    {
        $areaCode = $this->getLayout()->getArea();
        $area = Mage::app()->getArea($areaCode);
        $area->load(Mage_Core_Model_App_Area::PART_CONFIG)
            ->load(Mage_Core_Model_App_Area::PART_EVENTS);
        $this->_initDefaultTheme($areaCode);
        $area->detectDesign($this->getRequest());
        $area->load(Mage_Core_Model_App_Area::PART_TRANSLATE);
        return $this;
    }

    /**
     * Initialize theme
     *
     * @param string $areaCode
     * @return Mage_Install_Controller_Action
     */
    protected function _initDefaultTheme($areaCode)
    {
        /** @var $design Mage_Core_Model_View_DesignInterface */
        $design = Mage::getObjectManager()->get('Mage_Core_Model_View_DesignInterface');
        /** @var $themesCollection Mage_Core_Model_Theme_Collection */
        $themesCollection = Mage::getObjectManager()->create('Mage_Core_Model_Theme_Collection');
        $themeModel = $themesCollection->addDefaultPattern($areaCode)
            ->addFilter('theme_path', $design->getConfigurationDesignTheme($areaCode))
            ->getFirstItem();
        $design->setArea($areaCode)->setDesignTheme($themeModel);
        return $this;
    }
}
