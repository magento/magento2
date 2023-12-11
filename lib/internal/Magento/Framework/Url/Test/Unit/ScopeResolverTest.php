<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Url\Test\Unit;

use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\ScopeInterface;
use Magento\Framework\Url\ScopeResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScopeResolverTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $scopeResolverMock;

    /**
     * @var MockObject
     */
    protected $_object;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->scopeResolverMock = $this->getMockBuilder(
            ScopeResolverInterface::class
        )->getMock();
        $this->_object = $objectManager->getObject(
            ScopeResolver::class,
            ['scopeResolver' => $this->scopeResolverMock]
        );
    }

    /**
     * @param int|null$scopeId
     *
     * @return void
     * @dataProvider getScopeDataProvider
     */
    public function testGetScope($scopeId): void
    {
        $scopeMock = $this->getMockBuilder(ScopeInterface::class)
            ->getMock();
        $this->scopeResolverMock
            ->method('getScope')
            ->with($scopeId)
            ->willReturn($scopeMock);
        $this->_object->getScope($scopeId);
    }

    /**
     * @return void
     */
    public function testGetScopeException(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The scope object is invalid. Verify the scope object and try again.');
        $this->_object->getScope();
    }

    /**
     * @return array
     */
    public function getScopeDataProvider(): array
    {
        return [[null], [1]];
    }

    /**
     * @return void
     */
    public function testGetScopes(): void
    {
        $this->scopeResolverMock->expects($this->once())->method('getScopes');
        $this->_object->getScopes();
    }
}
