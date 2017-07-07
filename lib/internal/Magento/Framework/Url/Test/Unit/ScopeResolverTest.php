<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url\Test\Unit;

class ScopeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->scopeResolverMock = $this->getMockBuilder(
            \Magento\Framework\App\ScopeResolverInterface::class
        )->getMock();
        $this->_object = $objectManager->getObject(
            \Magento\Framework\Url\ScopeResolver::class,
            ['scopeResolver' => $this->scopeResolverMock]
        );
    }

    /**
     * @dataProvider getScopeDataProvider
     * @param int|null$scopeId
     */
    public function testGetScope($scopeId)
    {
        $scopeMock = $this->getMockBuilder(\Magento\Framework\Url\ScopeInterface::class)->getMock();
        $this->scopeResolverMock->expects(
            $this->at(0)
        )->method(
            'getScope'
        )->with(
            $scopeId
        )->will(
            $this->returnValue($scopeMock)
        );
        $this->_object->getScope($scopeId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid scope object
     */
    public function testGetScopeException()
    {
        $this->_object->getScope();
    }

    /**
     * @return array
     */
    public function getScopeDataProvider()
    {
        return [[null], [1]];
    }

    public function testGetScopes()
    {
        $this->scopeResolverMock->expects($this->once())->method('getScopes');
        $this->_object->getScopes();
    }
}
