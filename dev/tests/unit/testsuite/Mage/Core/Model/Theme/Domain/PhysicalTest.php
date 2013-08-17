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
 * Test theme domain physical model
 */
class Mage_Core_Model_Theme_Domain_PhysicalTest extends PHPUnit_Framework_TestCase
{
    public function testCreateVirtualTheme()
    {
        $physicalTheme = $this->getMock('Mage_Core_Model_Theme', null, array(), '', false, false);
        $physicalTheme->setData(array(
            'parent_id' => 10,
            'theme_title' => 'Test Theme'
        ));

        $copyService = $this->getMock('Mage_Core_Model_Theme_CopyService', array('copy'), array(), '', false, false);
        $copyService->expects($this->once())
            ->method('copy')
            ->will($this->returnValue($copyService));

        $virtualTheme = $this->getMock(
            'Mage_Core_Model_Theme', array('getThemeImage', 'createPreviewImageCopy', 'save'),
            array(), '', false, false
        );
        $virtualTheme->expects($this->once())
            ->method('getThemeImage')
            ->will($this->returnValue($virtualTheme));

        $virtualTheme->expects($this->once())
            ->method('createPreviewImageCopy')
            ->will($this->returnValue($virtualTheme));

        $virtualTheme->expects($this->once())
            ->method('save')
            ->will($this->returnValue($virtualTheme));

        $themeFactory = $this->getMock('Mage_Core_Model_ThemeFactory', array('create'), array(), '', false, false);
        $themeFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($virtualTheme));

        $themeCollection = $this->getMock(
            'Mage_Core_Model_Resource_Theme_Collection', array('addTypeFilter', 'addAreaFilter', 'addFilter', 'count'),
            array(), '', false, false
        );

        $themeCollection->expects($this->any())
            ->method('addTypeFilter')
            ->will($this->returnValue($themeCollection));

        $themeCollection->expects($this->any())
            ->method('addAreaFilter')
            ->will($this->returnValue($themeCollection));

        $themeCollection->expects($this->any())
            ->method('addFilter')
            ->will($this->returnValue($themeCollection));

        $themeCollection->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        $domainModel = new Mage_Core_Model_Theme_Domain_Physical(
            $this->getMock('Mage_Core_Model_Theme', array(), array(), '', false, false),
            $themeFactory,
            $this->getMock('Mage_Core_Helper_Data', array(), array(), '', false, false),
            $copyService,
            $themeCollection
        );
        $domainModel->createVirtualTheme($physicalTheme);
    }
}
