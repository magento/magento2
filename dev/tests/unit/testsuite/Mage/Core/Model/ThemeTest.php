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
     * @param string $designDir
     * @param string $targetPath
     * @return mixed
     */
    protected function _getThemeModel($designDir, $targetPath)
    {
        Mage::getConfig()->getOptions()->setData('design_dir', $designDir);
        $objectManager = Mage::getObjectManager();

        /** @var $themeCollection Mage_Core_Model_Resource_Theme_Collection */
        $themeCollection = $this->getMock('Mage_Core_Model_Resource_Theme_Collection', array(), array(), '', false);
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            Magento_Test_Helper_ObjectManager::MODEL_ENTITY, 'Mage_Core_Model_Theme',
            array(
                'objectManager'      => $objectManager,
                'helper'             => $objectManager->get('Mage_Core_Helper_Data'),
                'resource'           => $objectManager->get('Mage_Core_Model_Resource_Theme'),
                'resourceCollection' => $themeCollection,
                'themeFactory'       => $objectManager->get('Mage_Core_Model_Theme_Factory'),
            )
        );
        /** @var $themeMock Mage_Core_Model_Theme */
        $themeMock = $this->getMock('Mage_Core_Model_Theme', array('_init'), $arguments, '', true);
        $filesystemMock = $this->getMockBuilder('Magento_Filesystem')->disableOriginalConstructor(true)->getMock();
        $filesystemMock->expects($this->any())->method('searchKeys')
            ->will($this->returnValueMap(array(
                array(
                    $designDir, 'frontend/default/iphone/theme.xml',
                    array(
                        str_replace('/', DIRECTORY_SEPARATOR, $designDir . '/frontend/default/iphone/theme.xml')
                    )
                ),
                array(
                    $designDir, 'frontend/default/iphone/theme_invalid.xml',
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

        /** @var $collectionMock Mage_Core_Model_Theme_Collection|PHPUnit_Framework_MockObject_MockObject */
        $collectionMock = $this->getMock('Mage_Core_Model_Theme_Collection', array('getNewEmptyItem'),
            array($filesystemMock));
        $collectionMock->expects($this->any())
            ->method('getNewEmptyItem')
            ->will($this->returnValue($themeMock));

        return $collectionMock->setBaseDir($designDir)->addTargetPattern($targetPath)->getFirstItem();
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
            $this->_getThemeModel($designDir, $targetPath)->getData()
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
            $this->_getThemeModel($designDir, $targetPath)->getData()
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
}
