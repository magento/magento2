<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Parser\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class XmlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Dependency\Parser\Config\Xml
     */
    protected $parser;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->parser = $objectManagerHelper->getObject(\Magento\Setup\Module\Dependency\Parser\Config\Xml::class);
    }

    /**
     * @param array $options
     * @dataProvider dataProviderWrongOptionFilesForParse
     */
    public function testParseWithWrongOptionFilesForParse($options)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Parse error: Option "files_for_parse" is wrong.');

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
