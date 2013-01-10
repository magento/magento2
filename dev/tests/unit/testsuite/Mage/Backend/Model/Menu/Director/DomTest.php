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
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Backend_Model_Menu_Director_Dom
 */
class Mage_Backend_Model_Menu_Director_DomTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Mage_Backend_Model_Menu_Director_Dom
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_loggerMock;

    public function setUp()
    {
        $basePath = realpath(__DIR__)  . '/../../_files/';
        $path = $basePath . 'menu_merged.xml';
        $domDocument = new DOMDocument();
        $domDocument->load($path);

        $mockCommand = $this->getMockForAbstractClass(
            'Mage_Backend_Model_Menu_Builder_CommandAbstract',
            array(),
            '',
            false,
            true,
            true,
            array('getId')
        );

        $factory = $this->getMock('Magento_ObjectManager_Zend', array(), array(), '', false);
        $factory->expects($this->any())->method('create')->will($this->returnValue($mockCommand));

        $this->_loggerMock = $this->getMock('Mage_Core_Model_Logger', array('log'), array(), '', false);

        $this->_model = new Mage_Backend_Model_Menu_Director_Dom(
            $domDocument,
            $factory,
            $this->_loggerMock
        );
    }

    /**
     * Test data extracted from DOMDocument
     */
    public function testExtractData()
    {
        $basePath = realpath(__DIR__)  . '/../../_files/';
        $expectedData = include ($basePath . 'menu_merged.php');
        $this->assertEquals($expectedData, $this->_model->getExtractedData(), 'Invalid extracted data');
    }

    /**
     * Test command method with valid builder
     */
    public function testCommandWithValidBuilder()
    {
        $builder = $this->getMock('Mage_Backend_Model_Menu_Builder', array('processCommand'), array(), '', false);
        $builder->expects($this->exactly(8))->method('processCommand');
        $this->assertInstanceOf('Mage_Backend_Model_Menu_DirectorAbstract', $this->_model->buildMenu($builder));
    }

    public function testCommandLogging()
    {
        $this->_loggerMock->expects($this->exactly(4))->method('log');
        $builder = $this->getMock('Mage_Backend_Model_Menu_Builder', array(), array(), '', false);
        $this->_model->buildMenu($builder);
    }
}
