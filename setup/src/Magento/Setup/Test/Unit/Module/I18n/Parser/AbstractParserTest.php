<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Parser;

class AbstractParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\I18n\Parser\AbstractParser|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_parserMock;

    protected function setUp()
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
        $this->setExpectedException('InvalidArgumentException', $message);

        $this->_parserMock->addAdapter(
            'php',
            $this->getMock(\Magento\Setup\Module\I18n\Parser\AdapterInterface::class)
        );
        $this->_parserMock->parse($options);
    }

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
        $this->assertInternalType('array', $this->_parserMock->getPhrases());
    }
}
