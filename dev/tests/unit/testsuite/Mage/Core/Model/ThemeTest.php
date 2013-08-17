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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test theme model
 */
class Mage_Core_Model_ThemeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Theme|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_imageFactory;

    protected function setUp()
    {
        $customizationConfig = $this->getMock('Mage_Theme_Model_Config_Customization', array(), array(), '', false);
        $customizationFactory = $this->getMock('Mage_Core_Model_Theme_CustomizationFactory',
            array('create'), array(), '', false);
        $resourceCollection = $this->getMock('Mage_Core_Model_Resource_Theme_Collection', array(), array(), '', false);
        $this->_imageFactory = $this->getMock('Mage_Core_Model_Theme_ImageFactory',
            array('create'), array(), '', false);

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments('Mage_Core_Model_Theme', array(
            'customizationFactory' => $customizationFactory,
            'customizationConfig'  => $customizationConfig,
            'imageFactory'         => $this->_imageFactory,
            'resourceCollection'   => $resourceCollection
        ));

        $this->_model = $objectManagerHelper->getObject('Mage_Core_Model_Theme', $arguments);
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * Expected theme data from configuration
     *
     * @return array
     */
    protected function _expectedThemeDataFromConfiguration()
    {
        return array(
            'parent_id'            => null,
            'theme_path'           => 'default/iphone',
            'theme_version'        => '2.0.0.1',
            'theme_title'          => 'Iphone',
            'preview_image'        => 'images/preview.png',
            'magento_version_from' => '2.0.0.1-dev1',
            'magento_version_to'   => '*',
            'is_featured'          => true,
            'theme_directory'      => implode(DIRECTORY_SEPARATOR,
                array(__DIR__, '_files', 'frontend', 'default', 'iphone')),
            'parent_theme_path'    => null,
            'area'                 => 'frontend',
            'code'                 => 'default/iphone',
        );
    }

    /**
     * @covers Mage_Core_Model_Theme::getThemeImage
     */
    public function testThemeImageGetter()
    {
        $this->_imageFactory->expects($this->once())->method('create')->with(array('theme' => $this->_model));
        $this->_model->getThemeImage();
    }

    /**
     * @dataProvider isVirtualDataProvider
     * @param int $type
     * @param string $isVirtual
     * @covers Mage_Core_Model_Theme::isVirtual
     */
    public function testIsVirtual($type, $isVirtual)
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = $this->getMock('Mage_Core_Model_Theme', null, array(), '', false);
        $themeModel->setType($type);
        $this->assertEquals($isVirtual, $themeModel->isVirtual());
    }

    /**
     * @return array
     */
    public function isVirtualDataProvider()
    {
        return array(
            array('type' => Mage_Core_Model_Theme::TYPE_VIRTUAL, 'isVirtual' => true),
            array('type' => Mage_Core_Model_Theme::TYPE_STAGING, 'isVirtual' => false),
            array('type' => Mage_Core_Model_Theme::TYPE_PHYSICAL, 'isVirtual' => false)
        );
    }

    /**
     * @dataProvider isPhysicalDataProvider
     * @param int $type
     * @param string $isPhysical
     * @covers Mage_Core_Model_Theme::isPhysical
     */
    public function testIsPhysical($type, $isPhysical)
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = $this->getMock('Mage_Core_Model_Theme', null, array(), '', false);
        $themeModel->setType($type);
        $this->assertEquals($isPhysical, $themeModel->isPhysical());
    }

    /**
     * @return array
     */
    public function isPhysicalDataProvider()
    {
        return array(
            array('type' => Mage_Core_Model_Theme::TYPE_VIRTUAL, 'isPhysical' => false),
            array('type' => Mage_Core_Model_Theme::TYPE_STAGING, 'isPhysical' => false),
            array('type' => Mage_Core_Model_Theme::TYPE_PHYSICAL, 'isPhysical' => true)
        );
    }

    /**
     * @dataProvider isVisibleDataProvider
     * @param int $type
     * @param string $isVisible
     * @covers Mage_Core_Model_Theme::isVisible
     */
    public function testIsVisible($type, $isVisible)
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = $this->getMock('Mage_Core_Model_Theme', null, array(), '', false);
        $themeModel->setType($type);
        $this->assertEquals($isVisible, $themeModel->isVisible());
    }

    /**
     * @return array
     */
    public function isVisibleDataProvider()
    {
        return array(
            array('type' => Mage_Core_Model_Theme::TYPE_VIRTUAL, 'isVisible' => true),
            array('type' => Mage_Core_Model_Theme::TYPE_STAGING, 'isVisible' => false),
            array('type' => Mage_Core_Model_Theme::TYPE_PHYSICAL, 'isVisible' => true)
        );
    }

    /**
     * Test id deletable
     *
     * @dataProvider isDeletableDataProvider
     * @param string $themeType
     * @param bool $isDeletable
     * @covers Mage_Core_Model_Theme::isDeletable
     */
    public function testIsDeletable($themeType, $isDeletable)
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = $this->getMock('Mage_Core_Model_Theme', array('getType'), array(), '', false);
        $themeModel->expects($this->once())
            ->method('getType')
            ->will($this->returnValue($themeType));
        $this->assertEquals($isDeletable, $themeModel->isDeletable());
    }

    /**
     * @return array
     */
    public function isDeletableDataProvider()
    {
        return array(
            array(Mage_Core_Model_Theme::TYPE_VIRTUAL, true),
            array(Mage_Core_Model_Theme::TYPE_STAGING, true),
            array(Mage_Core_Model_Theme::TYPE_PHYSICAL, false)
        );
    }

    public function testIsThemeCompatible()
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = $this->getMock('Mage_Core_Model_Theme', null, array(), '', false);

        $themeModel->setMagentoVersionFrom('2.0.0.0')->setMagentoVersionTo('*');
        $this->assertFalse($themeModel->isThemeCompatible());

        $themeModel->setMagentoVersionFrom('1.0.0.0')->setMagentoVersionTo('*');
        $this->assertTrue($themeModel->isThemeCompatible());
    }

    /**
     * @param mixed $originalCode
     * @param string $expectedCode
     * @dataProvider getCodeDataProvider
     */
    public function testGetCode($originalCode, $expectedCode)
    {
        $this->_model->setCode($originalCode);
        $this->assertSame($expectedCode, $this->_model->getCode());
    }

    /**
     * @return array
     */
    public function getCodeDataProvider()
    {
        return array(
            'string code' => array('theme/code', 'theme/code'),
            'null code'   => array(null, ''),
            'number code' => array(10, '10'),
        );
    }
}
