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
namespace Magento\Framework\Event\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event\Config\Converter
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_filePath;

    /**
     * @var \DOMDocument
     */
    protected $_source;

    protected function setUp()
    {
        $this->_filePath = __DIR__ . '/_files/';
        $this->_source = new \DOMDocument();
        $this->_model = new \Magento\Framework\Event\Config\Converter();
    }

    public function testConvert()
    {
        $this->_source->loadXML(file_get_contents($this->_filePath . 'event_config.xml'));
        $convertedFile = include $this->_filePath . 'event_config.php';
        $this->assertEquals($convertedFile, $this->_model->convert($this->_source));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Attribute name is missed
     */
    public function testConvertThrowsExceptionWhenDomIsInvalid()
    {
        $this->_source->loadXML(file_get_contents($this->_filePath . 'event_invalid_config.xml'));
        $this->_model->convert($this->_source);
    }
}
