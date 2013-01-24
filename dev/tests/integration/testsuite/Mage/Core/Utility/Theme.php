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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core theme utility
 */
class Mage_Core_Utility_Theme
{
    /**
     * @var string
     */
    protected $_designDir;

    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_design;

    /**
     * @var Mage_Core_Model_Theme_Registration
     */
    protected $_register;

    /**
     * @var Mage_Core_Model_Theme
     */
    protected $_theme;

    /**
     * @var Mage_Core_Model_Resource_Theme_Collection
     */
    protected $_themesCollection;

    /**
     * @param string $designDir
     * @param Mage_Core_Model_Design_Package $design
     * @param Mage_Core_Model_Theme_Registration $registration
     * @param Mage_Core_Model_Theme $theme
     */
    public function __construct(
        $designDir = null,
        Mage_Core_Model_Design_Package $design = null,
        Mage_Core_Model_Theme_Registration $registration,
        Mage_Core_Model_Theme $theme
    ) {
        $this->_designDir = $designDir;
        $this->_design = $design ? $design : Mage::getDesign();
        $this->_register = $registration;
        $this->_theme = $theme;
    }

    /**
     * @return Mage_Core_Model_Design_Package
     */
    public function getDesign()
    {
        return $this->_design;
    }

    /**
     * Register mocked package model in di
     *
     * @static
     */
    public static function registerDesignMock()
    {
        /** @var $packageMock Mage_Core_Model_Design_Package|PHPUnit_Framework_MockObject_MockObject */
        $packageMock = PHPUnit_Framework_MockObject_Generator::getMock(
            'Mage_Core_Model_Design_Package', array('getConfigurationDesignTheme'),
            array(self::_createFilesystem())
        );
        $package = Mage::getModel('Mage_Core_Model_Design_Package', array('filesystem' => self::_createFilesystem()));

        $callBackFixture = function ($area, $params) use ($package, $packageMock) {
            $area = $area ? $area : $packageMock->getArea();
            if (isset($params['useId']) && $params['useId'] === false) {
                return $package->getConfigurationDesignTheme($area, $params);
            } else {
                $params['useId'] = false;
                /** @var $package Mage_Core_Model_Design_Package */
                $configPath = $package->getConfigurationDesignTheme($area, $params);
                return Mage_Core_Utility_Theme::getTheme($configPath, $area)->getId();
            }
        };

        $packageMock->expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
            ->method('getConfigurationDesignTheme')
            ->will(new PHPUnit_Framework_MockObject_Stub_ReturnCallback($callBackFixture));

        /** @var $objectManager Magento_Test_ObjectManager */
        $objectManager = Mage::getObjectManager();
        $objectManager->addSharedInstance($packageMock, 'Mage_Core_Model_Design_Package');
    }


    /**
     * @return Magento_Filesystem
     */
    protected static function _createFilesystem()
    {
        return new Magento_Filesystem(new Magento_Filesystem_Adapter_Local());
    }

    /**
     * @return Mage_Core_Utility_Theme
     */
    public function registerThemes()
    {
        Mage::app()->getConfig()->getOptions()->setDesignDir($this->_designDir);
        $this->_register->register();
        $this->_design->setDefaultDesignTheme();
        return $this;
    }

    /**
     * @return Mage_Core_Model_Resource_Theme_Collection
     */
    protected function _getCollection()
    {
        if (!$this->_themesCollection) {
            $this->_themesCollection = $this->_theme->getCollection()->load();
        }
        return $this->_themesCollection;
    }

    /**
     * @param string $themePath
     * @param string|null $area
     * @return Mage_Core_Model_Theme
     */
    public function getThemeByParams($themePath, $area)
    {
        /** @var $theme Mage_Core_Model_Theme */
        foreach ($this->_getCollection() as $theme) {
            if ($theme->getThemePath() === $themePath && $theme->getArea() === $area) {
                return $theme;
            }
        }
        return $this->_theme;
    }

    /**
     * @param string $themePath
     * @param string|null $area
     * @return Mage_Core_Model_Theme
     */
    public static function getTheme($themePath, $area)
    {
        /** @var $theme Mage_Core_Model_Theme */
        $theme = Mage::getSingleton('Mage_Core_Model_Theme');
        $collection = $theme->getCollection()
            ->addFieldToFilter('theme_path', $themePath)
            ->addFieldToFilter('area', $area)
            ->load();
        return $collection->getFirstItem();
    }

    /**
     * @param string $themePath
     * @param null $area
     * @return Mage_Core_Utility_Theme
     */
    public function setDesignTheme($themePath, $area = null)
    {
        if (empty($area)) {
            $area = $this->_design->getArea();
        }
        $theme = $this->getThemeByParams($themePath, $area);
        $this->_design->setDesignTheme($theme, $area);
        return $this;
    }

    /**
     * @return array
     */
    public function getStructure()
    {
        $structure = array();
        /** @var $theme Mage_Core_Model_Theme */
        foreach ($this->_getCollection() as $theme) {
            if ($theme->getId() && $theme->getThemePath()) {
                $structure[$theme->getArea()][$theme->getThemePath()] = $theme;
            }
        }
        return $structure;
    }
}
