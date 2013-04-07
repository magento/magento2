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
 * Test theme virtual model
 */
class Mage_Core_Model_Theme_Domain_VirtualTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test get existing staging theme
     *
     * @covers Mage_Core_Model_Theme_Domain_Virtual::getStagingTheme
     */
    public function testGetStagingThemeExisting()
    {
        $themeStaging = $this->getMock('Mage_Core_Model_Theme', array(), array(), '', false, false);

        $theme = $this->getMock('Mage_Core_Model_Theme', array('getStagingVersion'), array(), '', false, false);
        $theme->expects($this->once())->method('getStagingVersion')->will($this->returnValue($themeStaging));

        $themeFactory = $this->getMock('Mage_Core_Model_Theme_Factory', array('create'), array(), '', false);
        $themeFactory->expects($this->never())->method('create');

        $themeCopyService = $this->getMock('Mage_Core_Model_Theme_CopyService', array('copy'), array(), '', false);
        $themeCopyService->expects($this->never())->method('copy');

        $service = $this->getMock('Mage_Core_Model_Theme_Service', array(), array(), '', false);

        $object = new Mage_Core_Model_Theme_Domain_Virtual($theme, $themeFactory, $themeCopyService, $service);

        $this->assertSame($themeStaging, $object->getStagingTheme());
        $this->assertSame($themeStaging, $object->getStagingTheme());
    }

    /**
     * Test creating staging theme
     *
     * @covers Mage_Core_Model_Theme_Domain_Virtual::getStagingTheme
     */
    public function testGetStagingThemeNew()
    {
        $theme = $this->getMock('Mage_Core_Model_Theme', array('getStagingVersion'), array(), '', false, false);
        $theme->expects($this->once())->method('getStagingVersion')->will($this->returnValue(null));
        /** @var $theme Varien_Object */
        $theme->setData(array(
            'id'                    => 'fixture_theme_id',
            'theme_version'         => 'fixture_theme_version',
            'theme_title'           => 'fixture_theme_title',
            'preview_image'         => 'fixture_preview_image',
            'magento_version_from'  => 'fixture_magento_version_from',
            'magento_version_to'    => 'fixture_magento_version_to',
            'is_featured'           => 'fixture_is_featured',
            'area'                  => 'fixture_area',
            'type'                  => Mage_Core_Model_Theme::TYPE_VIRTUAL
        ));

        $themeStaging = $this->getMock('Mage_Core_Model_Theme', array('setData', 'save'), array(), '', false, false);
        $themeStaging->expects($this->at(0))->method('setData')->with(array(
            'parent_id'             => 'fixture_theme_id',
            'theme_path'            => null,
            'theme_version'         => 'fixture_theme_version',
            'theme_title'           => 'fixture_theme_title - Staging',
            'preview_image'         => 'fixture_preview_image',
            'magento_version_from'  => 'fixture_magento_version_from',
            'magento_version_to'    => 'fixture_magento_version_to',
            'is_featured'           => 'fixture_is_featured',
            'area'                  => 'fixture_area',
            'type'                  => Mage_Core_Model_Theme::TYPE_STAGING,
        ));
        $themeStaging->expects($this->at(1))->method('save');

        $themeFactory = $this->getMock('Mage_Core_Model_Theme_Factory', array(), array(), '', false);
        $themeFactory->expects($this->once())->method('create')->will($this->returnValue($themeStaging));

        $themeCopyService = $this->getMock('Mage_Core_Model_Theme_CopyService', array('copy'), array(), '', false);
        $themeCopyService->expects($this->once())->method('copy')->with($theme, $themeStaging);

        $service = $this->getMock('Mage_Core_Model_Theme_Service', array(), array(), '', false);

        $object = new Mage_Core_Model_Theme_Domain_Virtual($theme, $themeFactory, $themeCopyService, $service);

        $this->assertSame($themeStaging, $object->getStagingTheme());
        $this->assertSame($themeStaging, $object->getStagingTheme());
    }

    /**
     * Test for is assigned method
     *
     * @covers Mage_Core_Model_Theme_Domain_Virtual::isAssigned
     */
    public function testIsAssigned()
    {
        $themeServiceMock = $this->getMock('Mage_Core_Model_Theme_Service', array(), array(), '', false);
        $themeMock = $this->getMock('Mage_Core_Model_Theme', array('getCollection', 'getId'), array(), '', false,
            false);
        $themeServiceMock->expects($this->atLeastOnce())->method('isThemeAssignedToStore')
            ->with($themeMock)
            ->will($this->returnValue(true));
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments('Mage_Core_Model_Theme_Domain_Virtual',
            array(
                'theme' => $themeMock,
                'service' => $themeServiceMock,
            )
        );
        /** @var $model Mage_Core_Model_Theme_Domain_Virtual */
        $model = $objectManagerHelper->getObject('Mage_Core_Model_Theme_Domain_Virtual', $constructArguments);
        $this->assertEquals(true, $model->isAssigned());
    }
}
