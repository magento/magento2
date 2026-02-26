<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Dependency\Parser;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Dependency\Parser\Code;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class CodeTest extends TestCase
{
    /**
     * @var Code
     */
    protected $parser;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->parser = $objectManagerHelper->getObject(Code::class);
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
            [['files_for_parse' => [], 'declared_namespaces' => [1, 2]]],
            [['files_for_parse' => 'sting', 'declared_namespaces' => [1, 2]]],
            [['there_are_no_files_for_parse' => [1, 3], 'declared_namespaces' => [1, 2]]]
        ];
    }

    /**
     * @param array $options
     */
    #[DataProvider('dataProviderWrongOptionDeclaredNamespace')]
    public function testParseWithWrongOptionDeclaredNamespace($options)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Parse error: Option "declared_namespaces" is wrong.');
        $this->parser->parse($options);
    }

    /**
     * @return array
     */
    public static function dataProviderWrongOptionDeclaredNamespace()
    {
        return [
            [['declared_namespaces' => [], 'files_for_parse' => [1, 2]]],
            [['declared_namespaces' => 'sting', 'files_for_parse' => [1, 2]]],
            [['there_are_no_declared_namespaces' => [1, 3], 'files_for_parse' => [1, 2]]]
        ];
    }
}
