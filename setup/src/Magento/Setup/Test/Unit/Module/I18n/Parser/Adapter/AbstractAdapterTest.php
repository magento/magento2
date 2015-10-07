<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter;

class AbstractAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\I18n\Parser\Adapter\AbstractAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapterMock;

    /**
     * @var \Magento\Setup\Module\I18n\Parser\Adapter\AbstractAdapter
     */
    protected $_adapterReflection;

    protected function setUp()
    {
        $this->_adapterMock = $this->getMockForAbstractClass(
            'Magento\Setup\Module\I18n\Parser\Adapter\AbstractAdapter',
            [],
            '',
            false,
            true,
            true,
            ['_parse']
        );
        $this->_adapterReflection = new \ReflectionMethod(
            'Magento\Setup\Module\I18n\Parser\Adapter\AbstractAdapter',
            '_addPhrase'
        );
        $this->_adapterReflection->setAccessible(true);
    }

    public function testParse()
    {
        $this->_adapterMock->expects($this->once())->method('_parse');

        $this->_adapterMock->parse('file1');
    }

    public function getPhrases()
    {
        $this->assertInternalType('array', $this->_adapterMock->getPhrases());
    }

    public function testAddPhrase()
    {
        $phrase = 'test phrase';
        $line = 2;
        $expected = [
            [
                'phrase' => $phrase,
                'file' => null,
                'line' => $line,
                'quote' => ''
            ]
        ];
        $this->_adapterReflection->invoke($this->_adapterMock, $phrase, $line);
        $actual = $this->_adapterMock->getPhrases();
        $this->assertEquals($expected, $actual);

        $this->_adapterReflection->invoke($this->_adapterMock, '', '');
        $actual = $this->_adapterMock->getPhrases();
        $this->assertEquals($expected, $actual);
    }
}
