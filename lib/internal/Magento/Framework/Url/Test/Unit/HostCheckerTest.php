<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class HostCheckerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Url\HostChecker */
    private $object;

    /** @var \Magento\Framework\Url\ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $scopeResolver;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->scopeResolver = $this->getMockBuilder(
            \Magento\Framework\Url\ScopeResolverInterface::class
        )->getMock();

        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject(
            \Magento\Framework\Url\HostChecker::class,
            [
                'scopeResolver' => $this->scopeResolver
            ]
        );
    }

    /**
     * @dataProvider isOwnOriginDataProvider
     * @param string $url
     * @param boolean $result
     */
    public function testIsOwnOrigin($url, $result)
    {
        $scopes[0] = $this->getMockBuilder(\Magento\Framework\Url\ScopeInterface::class)->getMock();
        $scopes[0]->expects($this->any())->method('getBaseUrl')->willReturn('http://www.example.com');
        $scopes[1] = $this->getMockBuilder(\Magento\Framework\Url\ScopeInterface::class)->getMock();
        $scopes[1]->expects($this->any())->method('getBaseUrl')->willReturn('https://www.example2.com');

        $this->scopeResolver->expects($this->atLeastOnce())->method('getScopes')->willReturn($scopes);

        $this->assertEquals($result, $this->object->isOwnOrigin($url));
    }

    /**
     * @return array
     */
    public function isOwnOriginDataProvider()
    {
        return [
            ['http://www.example.com/some/page/', true],
            ['http://www.test.com/other/page/', false],
        ];
    }
}
