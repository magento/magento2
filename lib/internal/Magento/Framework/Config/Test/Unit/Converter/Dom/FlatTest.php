<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit\Converter\Dom;

use Magento\Framework\Config\Converter\Dom\Flat;
use Magento\Framework\Config\Dom\ArrayNodeConfig;
use Magento\Framework\Config\Dom\NodePathMatcher;
use PHPUnit\Framework\TestCase;

class FlatTest extends TestCase
{
    /**
     * @var Flat
     */
    protected $_model;

    /**
     * Path to fixtures
     *
     * @var string
     */
    protected $_fixturePath;

    protected function setUp(): void
    {
        $arrayNodeConfig = new ArrayNodeConfig(
            new NodePathMatcher(),
            [
                '/root/multipleNode' => 'id',
                '/root/wrongArray' => 'id',
            ],
            [
                '/root/node_one/subnode',
            ]
        );
        $this->_model = new Flat($arrayNodeConfig);
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

    public function testConvertWithNotUnique()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage(
            'Node path \'/root/someOtherVal\' is not unique, but it has not been marked as array.'
        );
        $dom = new \DOMDocument();
        $dom->load($this->_fixturePath . 'source_notuniq.xml');

        $this->_model->convert($dom);
    }

    public function testConvertWrongArray()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Array is expected to contain value for key \'id\'.');
        $dom = new \DOMDocument();
        $dom->load($this->_fixturePath . 'source_wrongarray.xml');

        $this->_model->convert($dom);
    }
}
