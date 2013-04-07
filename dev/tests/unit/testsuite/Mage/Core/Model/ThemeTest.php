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
     * Return Mock of Theme Model loaded from configuration
     *
     * @param bool $fromCollection
     * @param string $designDir
     * @param string $targetPath
     * @return Mage_Core_Model_Theme|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getThemeModel($fromCollection = false, $designDir = '', $targetPath = '')
    {
        $objectManager = $this->getMock('Magento_ObjectManager', array(), array(), '', false);
        $dirMock = $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false);
        $dirMock->expects($this->any())
            ->method('getDir')
            ->will($this->returnArgument(0));
        $objectManager->expects($this->any())
            ->method('get')
            ->with('Mage_Core_Model_Dir')
            ->will($this->returnValue($dirMock));

        /** @var $dirs Mage_Core_Model_Dir|PHPUnit_Framework_MockObject_MockObject */
        $dirs = $this->getMock('Mage_Core_Model_Dir', array('getDir'), array(), '', false);

        $dirs->expects($this->any())
            ->method('getDir')
            ->will($this->returnArgument(0));

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments('Mage_Core_Model_Theme',
            array(
                'objectManager' => $this->getMock('Magento_ObjectManager', array(), array(), '', false),
                'themeFactory' => $this->getMock('Mage_Core_Model_Theme_Factory', array(), array(), '', false),
                'helper' => $this->getMock('Mage_Core_Helper_Data', array(), array(), '', false),
                'themeImage' => $this->getMock('Mage_Core_Model_Theme_Image', array(), array(), '', false),
                //domain factory
                'dirs' => $dirs,
                'resource' => $this->getMock('Mage_Core_Model_Resource_Theme', array(), array(), '', false),
                'resourceCollection'
                    => $this->getMock('Mage_Core_Model_Resource_Theme_Collection', array(), array(), '', false),
            )
        );
        /** @var $themeMock Mage_Core_Model_Theme */
        $reflection = new \ReflectionClass('Mage_Core_Model_Theme');
        $themeMock = $reflection->newInstanceArgs($arguments);

        $objectManager->expects($this->any())
            ->method('create')
            ->with('Mage_Core_Model_Theme')
            ->will($this->returnValue($themeMock));

        if (!$fromCollection) {
            return $themeMock;
        }

        $filesystemMock = $this->getMockBuilder('Magento_Filesystem')->disableOriginalConstructor(true)->getMock();
        $filesystemMock->expects($this->any())->method('searchKeys')
            ->will($this->returnValueMap(array(
                array(
                    $designDir, str_replace('/', DIRECTORY_SEPARATOR, 'frontend/default/iphone/theme.xml'),
                    array(
                        str_replace('/', DIRECTORY_SEPARATOR, $designDir . '/frontend/default/iphone/theme.xml')
                    )
                ),
                array(
                    $designDir, str_replace('/', DIRECTORY_SEPARATOR, 'frontend/default/iphone/theme_invalid.xml'),
                    array(
                        str_replace(
                            '/',
                            DIRECTORY_SEPARATOR,
                            $designDir . '/frontend/default/iphone/theme_invalid.xml'
                        )
                    )
                ),
            )
        ));

        $themeCollection = new Mage_Core_Model_Theme_Collection($filesystemMock, $objectManager, $dirMock);

        return $themeCollection->setBaseDir($designDir)->addTargetPattern($targetPath)->getFirstItem();
    }

    /**
     * Test load from configuration
     *
     * @covers Mage_Core_Model_Theme::loadFromConfiguration
     */
    public function testLoadFromConfiguration()
    {
        $targetPath = implode(DIRECTORY_SEPARATOR, array('frontend', 'default', 'iphone', 'theme.xml'));
        $designDir = implode(DIRECTORY_SEPARATOR, array(__DIR__, '_files'));

        $this->assertEquals(
            $this->_expectedThemeDataFromConfiguration(),
            $this->_getThemeModel(true, $designDir, $targetPath)->getData()
        );
    }

    /**
     * Test load invalid configuration
     *
     * @covers Mage_Core_Model_Theme::loadFromConfiguration
     * @expectedException Magento_Exception
     */
    public function testLoadInvalidConfiguration()
    {
        $targetPath = implode(DIRECTORY_SEPARATOR, array('frontend', 'default', 'iphone', 'theme_invalid.xml'));
        $designDir = implode(DIRECTORY_SEPARATOR, array(__DIR__, '_files'));

        $this->assertEquals(
            $this->_expectedThemeDataFromConfiguration(),
            $this->_getThemeModel(true, $designDir, $targetPath)->getData()
        );
    }

    /**
     * Expected theme data from configuration
     *
     * @return array
     */
    public function _expectedThemeDataFromConfiguration()
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
        );
    }

    public function testSaveThemeCustomization()
    {
        $themeMock = $this->_getThemeModel();
        $jsFile = $this->getMock('Mage_Core_Model_Theme_Customization_Files_Js', array('saveData'), array(), '', false);
        $jsFile->expects($this->atLeastOnce())->method('saveData');

        $themeMock->setCustomization($jsFile);
        $this->assertInstanceOf('Mage_Core_Model_Theme', $themeMock->saveThemeCustomization());
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
     * @dataProvider checkThemeCompatibleDataProvider
     * @covers Mage_Core_Model_Theme::checkThemeCompatible
     */
    public function testCheckThemeCompatible($versionFrom, $versionTo, $title, $resultTitle)
    {
        $helper = $this->getMockBuilder('Mage_Core_Helper_Data')
            ->disableOriginalConstructor()
            ->setMethods(array('__'))
            ->getMock();
        $helper->expects($this->any())
            ->method('__')
            ->will($this->returnValue(sprintf('%s (incompatible version)', $title)));

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments('Mage_Core_Model_Theme', array(
            'helper' => $helper
        ));

        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = $this->getMock('Mage_Core_Model_Theme', null, $arguments);
        $themeModel->setMagentoVersionFrom($versionFrom)->setMagentoVersionTo($versionTo)->setThemeTitle($title);
        $themeModel->checkThemeCompatible();
        $this->assertEquals($resultTitle, $themeModel->getThemeTitle());
    }

    /**
     * @return array
     */
    public function checkThemeCompatibleDataProvider()
    {
        return array(
            array('2.0.0.0', '*', 'Title', 'Title (incompatible version)'),
            array('1.0.0.0', '*', 'Title', 'Title')
        );
    }

    /**
     * @dataProvider getThemeFilesPathDataProvider
     * @param string $type
     * @param string $expectedPath
     */
    public function testGetThemeFilesPath($type, $expectedPath)
    {
        $theme = $this->_getThemeModel();
        $theme->setId(123);
        $theme->setType($type);
        $theme->setArea('area51');
        $theme->setThemePath('theme_path');

        $this->assertEquals(
            $expectedPath,
            $theme->getThemeFilesPath()
        );
    }

    /**
     * @return array
     */
    public function getThemeFilesPathDataProvider()
    {
        return array(
            array(Mage_Core_Model_Theme::TYPE_PHYSICAL, 'design/area51/theme_path'),
            array(Mage_Core_Model_Theme::TYPE_VIRTUAL, 'media/theme_customization/123'),
            array(Mage_Core_Model_Theme::TYPE_STAGING, 'media/theme_customization/123'),
        );
    }

    /**
     * @param $customizationPath
     * @param $themeId
     * @param $expected
     * @dataProvider getCustomViewConfigDataProvider
     */
    public function testGetCustomViewConfigPath($customizationPath, $themeId, PHPUnit_Framework_Constraint $expected)
    {
        $themeMock = $this->_getThemeModel();
        $themeMock->setData('customization_path', $customizationPath);
        $themeMock->setId($themeId);
        $actual = $themeMock->getCustomViewConfigPath();
        $this->assertThat($actual, $expected);
    }

    /**
     * @return array
     */
    public function getCustomViewConfigDataProvider()
    {
        return array(
            'no custom path, theme is not loaded' => array(
                null, null, $this->isEmpty()
            ),
            'no custom path, theme is loaded' => array(
                null, 'theme_id', $this->equalTo('media/theme_customization/theme_id/view.xml')
            ),
            'with custom path, theme is not loaded' => array(
                'custom/path', null, $this->equalTo('custom/path/view.xml')
            ),
            'with custom path, theme is loaded' => array(
                'custom/path', 'theme_id', $this->equalTo('custom/path/view.xml')
            ),
        );
    }
}
