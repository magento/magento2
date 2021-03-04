<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Link;

use Magento\Catalog\Model\Product\Link\Resolver;

class ResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var Resolver
     */
    protected $resolver;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMockForAbstractClass();

        $this->resolver = new Resolver($this->requestMock);
    }

    public function testGetLinksEmpty()
    {
        $someLinks = [1, 2, 3];
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('links', [])
            ->willReturn($someLinks);
        $this->assertEquals($someLinks, $this->resolver->getLinks());
    }

    public function testGetLinksOverridden()
    {
        $overriddenLinks = [3, 5, 7];
        $this->requestMock->expects($this->never())
            ->method('getParam');

        $this->resolver->override($overriddenLinks);
        $this->assertEquals($overriddenLinks, $this->resolver->getLinks());
    }
}
