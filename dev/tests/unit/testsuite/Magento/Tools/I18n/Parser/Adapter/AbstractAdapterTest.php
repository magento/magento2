<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
