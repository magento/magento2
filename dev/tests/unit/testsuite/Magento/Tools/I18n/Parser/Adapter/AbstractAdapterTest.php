<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n\Parser\Adapter;

class AbstractAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\I18n\Parser\Adapter\AbstractAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapterMock;

    protected function setUp()
    {
        $this->_adapterMock = $this->getMockForAbstractClass(
            'Magento\Tools\I18n\Parser\Adapter\AbstractAdapter',
            [],
            '',
            false,
            true,
            true,
            ['_parse']
        );
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
}
