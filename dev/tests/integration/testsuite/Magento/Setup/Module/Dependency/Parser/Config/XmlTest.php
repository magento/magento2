<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Parser\Config;


class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $fixtureDir;

    /**
     * @var Xml
     */
    protected $parser;

    protected function setUp()
    {
        $this->fixtureDir = realpath(__DIR__ . '/../../_files') . '/';

        $this->parser = new Xml();
    }

    public function testParse()
    {
        $expected = [
            'Magento\Module1',
            'Magento\Module2',
        ];

        $actual = $this->parser->parse(
            ['files_for_parse' => [$this->fixtureDir . 'module1.xml', $this->fixtureDir . 'module2.xml']]
        );

        $this->assertEquals($expected, $actual);
    }
}
