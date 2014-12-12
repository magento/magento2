<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Test\Tools\Dependency\Parser\Config;

use Magento\Tools\Dependency\Parser\Config\Xml;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $fixtureDir;

    /**
     * @var \Magento\Tools\Dependency\Parser\Config\Xml
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
