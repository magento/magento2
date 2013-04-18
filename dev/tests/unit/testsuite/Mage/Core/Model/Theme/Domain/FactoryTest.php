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
 * Test theme domain model
 */
class Mage_Core_Model_Theme_Domain_FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Mage_Core_Model_Theme_Domain_Factory::create
     */
    public function testCreate()
    {
        $themeMock = $this->getMock('Mage_Core_Model_Theme', array('getType'), array(), '', false);
        $themeMock->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(Mage_Core_Model_Theme::TYPE_PHYSICAL));

        $newThemeMock = $this->getMock('Mage_Core_Model_Theme', array(), array(), '', false);

        $objectManager = $this->getMock('Magento_ObjectManager', array(), array('create'), '', false);
        $objectManager->expects($this->once())
            ->method('create')
            ->with('Mage_Core_Model_Theme_Domain_Physical', array('theme' => $themeMock))
            ->will($this->returnValue($newThemeMock));

        $themeDomainFactory = new Mage_Core_Model_Theme_Domain_Factory($objectManager);
        $this->assertEquals($newThemeMock, $themeDomainFactory->create($themeMock));
    }

    /**
     * @covers Mage_Core_Model_Theme_Domain_Factory::create
     */
    public function testCreateWithWrongThemeType()
    {
        $wrongThemeType = 'wrong_theme_type';
        $themeMock = $this->getMock('Mage_Core_Model_Theme', array('getType'), array(), '', false);
        $themeMock->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($wrongThemeType));

        $objectManager = $this->getMock('Magento_ObjectManager', array(), array('create'), '', false);

        $themeDomainFactory = new Mage_Core_Model_Theme_Domain_Factory($objectManager);

        $this->setExpectedException(
            'Mage_Core_Exception',
            sprintf('Invalid type of theme domain model "%s"', $wrongThemeType)
        );
        $themeDomainFactory->create($themeMock);
    }
}
