<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Test\Unit\Converter\Dom;

/**
 * Class DiFlatTest @covers \Magento\Framework\Config\Converter\Dom\DiFlat
 */
class DiFlatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test subject.
     *
     * @var \Magento\Framework\Config\Converter\Dom\DiFlat
     */
    private $model;

    /**
     * @inheritdoc
     */
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
        $this->model = new \Magento\Framework\Config\Converter\Dom\DiFlat($arrayNodeConfig);
    }

    /**
     * Test \Magento\Framework\Config\Converter\Dom\DiFlat::convert() exclude attribute 'translate'.
     *
     * @covers \Magento\Framework\Config\Converter\Dom\DiFlat::convert()
     */
    public function testConvert()
    {
        $fixturePath = __DIR__ . '/../../_files/converter/dom/flat/';
        $expected = require $fixturePath . 'result.php';

        $dom = new \DOMDocument();
        $dom->load($fixturePath . 'di_source.xml');

        $actual = $this->model->convert($dom);
        $this->assertEquals($expected, $actual);
    }
}
