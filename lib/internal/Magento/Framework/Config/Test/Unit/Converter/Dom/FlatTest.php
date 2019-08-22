<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit\Converter\Dom;

class FlatTest extends \PHPUnit\Framework\TestCase
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
            [
                '/root/multipleNode' => 'id',
                '/root/wrongArray' => 'id',
            ],
            [
                '/root/node_one/subnode',
            ]
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
