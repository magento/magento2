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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Config;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Config\View
     */
    protected $_model = null;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Config\View(
            array(
                file_get_contents(__DIR__ . '/_files/view_one.xml'),
                file_get_contents(__DIR__ . '/_files/view_two.xml')
            )
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructException()
    {
        new \Magento\Framework\Config\View(array());
    }

    public function testGetSchemaFile()
    {
        $this->assertFileExists($this->_model->getSchemaFile());
    }

    public function testGetVars()
    {
        $this->assertEquals(array('one' => 'Value One', 'two' => 'Value Two'), $this->_model->getVars('Two'));
    }

    public function testGetVarValue()
    {
        $this->assertFalse($this->_model->getVarValue('Unknown', 'nonexisting'));
        $this->assertEquals('Value One', $this->_model->getVarValue('Two', 'one'));
        $this->assertEquals('Value Two', $this->_model->getVarValue('Two', 'two'));
        $this->assertEquals('Value Three', $this->_model->getVarValue('Three', 'three'));
    }

    /**
     * @expectedException \Magento\Framework\Exception
     */
    public function testInvalidXml()
    {
        new \Magento\Framework\Config\View(array(file_get_contents(__DIR__ . '/_files/view_invalid.xml')));
    }
}
