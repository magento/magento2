<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Parser;

use Magento\Setup\Module\I18n\Parser\AbstractParser;
use Magento\Setup\Module\I18n\Parser\AdapterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AbstractParserTest extends TestCase
{
    /**
     * @var AbstractParser|MockObject
     */
    protected $_parserMock;

    protected function setUp(): void
    {
        $this->_parserMock = $this->getMockBuilder(AbstractParser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_parseByTypeOptions'])
            ->getMock();
    }

    /**
     * @param array $options
     * @param string $message
     */
    #[DataProvider('dataProviderForValidateOptions')]
    public function testValidateOptions($options, $message)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage($message);

        $this->_parserMock->addAdapter(
            'php',
            $this->createMock(AdapterInterface::class)
        );
        $this->_parserMock->parse($options);
    }

    /**
     * @return array
     */
    public static function dataProviderForValidateOptions()
    {
        return [
            [[['paths' => []]], 'Missed "type" in parser options.'],
            [[['type' => '', 'paths' => []]], 'Missed "type" in parser options.'],
            [
                [['type' => 'wrong_type', 'paths' => []]],
                'Adapter is not set for type "wrong_type".'
            ],
            [[['type' => 'php']], '"paths" in parser options must be array.'],
            [[['type' => 'php', 'paths' => '']], '"paths" in parser options must be array.']
        ];
    }

    public function getPhrases()
    {
        $this->assertIsArray($this->_parserMock->getPhrases());
    }
}
