<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Url\Test\Unit;

class ScopeResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeResolverMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_object;

    protected function setUp(): void
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
        )->willReturn(
            $scopeMock
        );
        $this->_object->getScope($scopeId);
    }

    /**
     */
    public function testGetScopeException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The scope object is invalid. Verify the scope object and try again.');

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
