<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Dependency\Parser\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Dependency\Parser\Config\Xml;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class XmlTest extends TestCase
{
    /**
     * @var Xml
     */
    protected $parser;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->parser = $objectManagerHelper->getObject(Xml::class);
    }

    /**
     * @param array $options
     */
    #[DataProvider('dataProviderWrongOptionFilesForParse')]
    public function testParseWithWrongOptionFilesForParse($options)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Parse error: Option "files_for_parse" is wrong.');
        $this->parser->parse($options);
    }

    /**
     * @return array
     */
    public static function dataProviderWrongOptionFilesForParse()
    {
        return [
            [['files_for_parse' => []]],
            [['files_for_parse' => 'sting']],
            [['there_are_no_files_for_parse' => [1, 3]]]
        ];
    }
}
