<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Parser\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\Dependency\Parser\Config\Xml
     */
    protected $parser;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->parser = $objectManagerHelper->getObject('Magento\Setup\Module\Dependency\Parser\Config\Xml');
    }

    /**
     * @param array $options
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parse error: Option "files_for_parse" is wrong.
     * @dataProvider dataProviderWrongOptionFilesForParse
     */
    public function testParseWithWrongOptionFilesForParse($options)
    {
        $this->parser->parse($options);
    }

    /**
     * @return array
     */
    public function dataProviderWrongOptionFilesForParse()
    {
        return [
            [['files_for_parse' => []]],
            [['files_for_parse' => 'sting']],
            [['there_are_no_files_for_parse' => [1, 3]]]
        ];
    }
}
