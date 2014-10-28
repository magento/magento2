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

/**
 * Test class for \Magento\Framework\File\Csv.
 */
namespace Magento\Framework\File;

class CsvTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Csv model
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\File\Csv();
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testSetLineLength()
    {
        $expected = 4;
        $this->_model->setLineLength($expected);
        $lineLengthProperty = new \ReflectionProperty(
            'Magento\Framework\File\Csv', '_lineLength'
        );
        $lineLengthProperty->setAccessible(true);
        $actual = $lineLengthProperty->getValue($this->_model);
        $this->assertEquals($expected, $actual);
    }

    public function testSetDelimiter()
    {
        $this->assertInstanceOf('\Magento\Framework\File\Csv', $this->_model->setDelimiter(','));
    }

    public function testSetEnclosure()
    {
        $this->assertInstanceOf('\Magento\Framework\File\Csv', $this->_model->setEnclosure('"'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage File "FileNameThatShouldNotExist" do not exists
     */
    public function testGetDataFileNonExistent()
    {
        $file = 'FileNameThatShouldNotExist';
        $this->_model->getData($file);
    }
}
