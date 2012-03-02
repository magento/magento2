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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Core
 */
class Mage_Core_Model_Design_Backend_ExceptionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Design_Backend_Exceptions
     */
    protected $_model = null;

    protected function setUp()
    {
        $this->_model = new Mage_Core_Model_Design_Backend_Exceptions;
        $this->_model->setScope('default');
        $this->_model->setScopeId(0);
        $this->_model->setPath('design/theme/ua_regexp');
    }

    /**
     * Basic test, checks that saved value contains all required entries and is saved as an array
     * @magentoDataFixture emptyFixture
     */
    public function testSaveValueIsFormedNicely()
    {
        $value = array(
            '1' => array('search' => '/Opera/', 'value' => 'default/default/blank'),
            '2' => array('search' => '/Firefox/', 'value' => 'default/default/blank')
        );

        $this->_model->setValue($value);
        $this->_model->save();

        $processedValue = unserialize($this->_model->getValue());
        $this->assertEquals(count($processedValue), 2, 'Number of saved values is wrong');

        $entry = $processedValue['1'];
        $this->assertArrayHasKey('search', $entry);
        $this->assertArrayHasKey('value', $entry);
        $this->assertArrayHasKey('regexp', $entry);
    }

    /**
     * @magentoDataFixture emptyFixture
     */
    public function testSaveEmptyValueIsSkipped()
    {
        $value = array(
            '1' => array('search' => '/Opera/', 'value' => 'default/default/blank'),
            '2' => array('search' => '', 'value' => 'default/default/blank'),
            '3' => array('search' => '/Firefox/', 'value' => 'default/default/blank')
        );

        $this->_model->setValue($value);
        $this->_model->save();

        $processedValue = unserialize($this->_model->getValue());
        $emptyIsSkipped = isset($processedValue['1']) && !isset($processedValue['2']) && isset($processedValue['3']);
        $this->assertTrue($emptyIsSkipped);
    }

    /**
     * @param array $designException
     * @param string $regexp
     * @dataProvider saveExceptionDataProvider
     * @magentoDataFixture emptyFixture
     */
    public function testSaveException($designException, $regexp)
    {
        $this->_model->setValue(array('1' => $designException));
        $this->_model->save();

        $processedValue = unserialize($this->_model->getValue());
        $this->assertEquals($processedValue['1']['regexp'], $regexp);
    }

    /**
     * @return array
     */
    public function saveExceptionDataProvider()
    {
        $result = array(
            array(
                array('search' => 'Opera', 'value' => 'default/default/blank'),
                '/Opera/i'
            ),
            array(
                array('search' => '/Opera/', 'value' => 'default/default/blank'),
                '/Opera/'
            ),
            array(
                array('search' => '#iPad|iPhone#i', 'value' => 'default/default/blank'),
                '#iPad|iPhone#i'
            ),
            array(
                array('search' => 'Mozilla (3.6+)/Firefox', 'value' => 'default/default/blank'),
                '/Mozilla \\(3\\.6\\+\\)\\/Firefox/i'
            )
        );

        return $result;
    }

    /**
     * @var array $value
     * @expectedException Mage_Core_Exception
     * @dataProvider saveWrongExceptionDataProvider
     * @magentoDataFixture emptyFixture
     */
    public function testSaveWrongException($value)
    {
        $this->_model->setValue($value);
        $this->_model->save();
    }

    /**
     * @return array
     */
    public function saveWrongExceptionDataProvider()
    {
        $result = array(
            array(array(
                '1' => array('search' => '/Opera/', 'value' => 'default/default/blank'),
                '2' => array('search' => '/invalid_regexp(/', 'value' => 'default/default/blank'),
            )),
            array(array(
                '1' => array('search' => '/invalid_regexp', 'value' => 'default/default/blank'),
                '2' => array('search' => '/Opera/', 'value' => 'default/default/blank'),
            )),
            array(array(
                '1' => array('search' => 'invalid_regexp/iU', 'value' => 'default/default/blank'),
                '2' => array('search' => '/Opera/', 'value' => 'default/default/blank'),
            )),
            array(array(
                '1' => array('search' => 'invalid_regexp#', 'value' => 'default/default/blank'),
                '2' => array('search' => '/Opera/', 'value' => 'default/default/blank'),
            )),
            array(array(
                '1' => array('search' => '/Firefox/', 'value' => 'default/default/blank'),
                '2' => array('search' => '/Opera/', 'value' => 'invalid_design'),
            )),
            array(array(
                '1' => array('search' => '/Firefox/'),
                '2' => array('search' => '/Opera/', 'value' => 'default/default/blank'),
            )),
            array(array(
                '1' => array('value' => 'default/default/blank'),
                '2' => array('search' => '/Opera/', 'value' => 'default/default/blank'),
            ))
        );

        return $result;
    }

    /**
     * Empty fixture that guarantee data isolation
     */
    public static function emptyFixture()
    {
    }
}
