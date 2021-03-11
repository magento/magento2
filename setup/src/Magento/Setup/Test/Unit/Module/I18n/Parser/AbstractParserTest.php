<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Parser;

class AbstractParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\I18n\Parser\AbstractParser|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_parserMock;

    protected function setUp(): void
    {
        $this->_parserMock = $this->getMockForAbstractClass(
            \Magento\Setup\Module\I18n\Parser\AbstractParser::class,
            [],
            '',
            false
        );
    }

    /**
     * @param array $options
     * @param string $message
     * @dataProvider dataProviderForValidateOptions
     */
    public function testValidateOptions($options, $message)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage($message);

        $this->_parserMock->addAdapter(
            'php',
            $this->createMock(\Magento\Setup\Module\I18n\Parser\AdapterInterface::class)
        );
        $this->_parserMock->parse($options);
    }

    /**
     * @return array
     */
    public function dataProviderForValidateOptions()
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
