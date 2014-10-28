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
namespace Magento\Framework\Config\Converter\Dom;

class FlatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Config\Converter\Dom\Flat
     */
    protected $_model;

    /**
     * Path to fixtures
     *
     * @var string
     */
    protected $_fixturePath;

    protected function setUp()
    {
        $arrayNodeConfig = new \Magento\Framework\Config\Dom\ArrayNodeConfig(
            new \Magento\Framework\Config\Dom\NodePathMatcher(),
            array(
                '/root/multipleNode' => 'id',
                '/root/wrongArray' => 'id',
            ),
            array(
                '/root/node_one/subnode',
            )
        );
        $this->_model = new \Magento\Framework\Config\Converter\Dom\Flat($arrayNodeConfig);
        $this->_fixturePath = realpath(__DIR__ . '/../../') . '/_files/converter/dom/flat/';
    }

    public function testConvert()
    {
        $expected = require $this->_fixturePath . 'result.php';

        $dom = new \DOMDocument();
        $dom->load($this->_fixturePath . 'source.xml');

        $actual = $this->_model->convert($dom);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Node path '/root/someOtherVal' is not unique, but it has not been marked as array.
     */
    public function testConvertWithNotUnique()
    {
        $dom = new \DOMDocument();
        $dom->load($this->_fixturePath . 'source_notuniq.xml');

        $this->_model->convert($dom);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Array is expected to contain value for key 'id'.
     */
    public function testConvertWrongArray()
    {
        $dom = new \DOMDocument();
        $dom->load($this->_fixturePath . 'source_wrongarray.xml');

        $this->_model->convert($dom);
    }
}
