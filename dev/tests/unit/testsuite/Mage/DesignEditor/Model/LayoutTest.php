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
 * @package     Mage_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Model_LayoutTest extends PHPUnit_Framework_TestCase
{
    /**#@+
     * Layout property names
     */
    const PROPERTY_SANITIZING = '_sanitationEnabled';
    const PROPERTY_WRAPPING   = '_wrappingEnabled';
    /**#@-*/

    /**
     * @var Mage_DesignEditor_Model_Layout
     */
    protected $_model;

    /**
     * Block and container restriction data
     *
     * @var array
     */
    protected $_restrictionData = array(
        'block' => array(
            'white_list' => array('Mage_Page_Block_'),
            'black_list' => array(),
        ),
        'container' => array(
            'white_list' => array('root'),
        ),
    );

    protected function setUp()
    {
        $this->_model = $this->_prepareLayoutObject();
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testSanitizeLayout()
    {
        $data = file_get_contents(__DIR__ . '/_files/sanitize.xml');
        $xml = new Varien_Simplexml_Element($data);
        $this->_model->sanitizeLayout($xml);
        $this->assertStringMatchesFormatFile(__DIR__ . '/_files/sanitize_expected.txt', $xml->asNiceXml());
    }

    /**
     * Create test layout with mocked arguments
     *
     * @return Mage_DesignEditor_Model_Layout
     */
    protected function _prepareLayoutObject()
    {
        $helper = $this->getMock(
            'Mage_DesignEditor_Helper_Data',
            array('getBlockWhiteList', 'getBlockBlackList', 'getContainerWhiteList'),
            array(), '', false
        );
        $helper->expects($this->any())
            ->method('getBlockWhiteList')
            ->will($this->returnValue($this->_restrictionData['block']['white_list']));
        $helper->expects($this->any())
            ->method('getBlockBlackList')
            ->will($this->returnValue($this->_restrictionData['block']['black_list']));
        $helper->expects($this->any())
            ->method('getContainerWhiteList')
            ->will($this->returnValue($this->_restrictionData['container']['white_list']));

        return new Mage_DesignEditor_Model_Layout(
            $this->getMock('Mage_Core_Model_BlockFactory', array(), array(), '', false),
            $this->getMock('Magento_Data_Structure', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Layout_Argument_Processor', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Layout_Translator', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Layout_ScheduledStructure', array(), array(), '', false),
            $this->getMock('Magento_Datasource_Factory', array(), array(), '', false),
            $helper
        );
    }

    /**
     * Test covers both setSanitizing and setWrapping methods in one test because of similar method logic
     *
     * @param string $property
     * @param bool $value
     * @throws InvalidArgumentException
     *
     * @dataProvider flagsDataProvider
     */
    public function testFlags($property, $value)
    {
        $this->_model = $this->_prepareLayoutObject();

        switch ($property) {
            case self::PROPERTY_SANITIZING:
                $this->_model->setSanitizing($value);
                break;

            case self::PROPERTY_WRAPPING:
                $this->_model->setWrapping($value);
                break;

            default:
                throw new InvalidArgumentException('Unknown property.');
        }

        $this->assertAttributeSame($value, $property, $this->_model);
    }

    /**
     * Data provider for testFlags
     *
     * @return array
     */
    public function flagsDataProvider()
    {
        return array(
            'sanitizing_true' => array(
                '$property' => self::PROPERTY_SANITIZING,
                '$value'    => true,
            ),
            'sanitizing_false' => array(
                '$property' => self::PROPERTY_SANITIZING,
                '$value'    => false,
            ),
            'wrapping_true' => array(
                '$property' => self::PROPERTY_WRAPPING,
                '$value'    => true,
            ),
            'wrapping_false' => array(
                '$property' => self::PROPERTY_WRAPPING,
                '$value'    => false,
            ),
        );
    }
}
