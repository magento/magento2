<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Url\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\HostChecker;
use Magento\Framework\Url\ScopeInterface;
use Magento\Framework\Url\ScopeResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HostCheckerTest extends TestCase
{
    /** @var HostChecker */
    private $object;

    /** @var ScopeResolverInterface|MockObject */
    private $scopeResolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeResolver = $this->getMockBuilder(
            ScopeResolverInterface::class
        )->getMock();

        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject(
            HostChecker::class,
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
        $scopes[0] = $this->getMockBuilder(ScopeInterface::class)
            ->getMock();
        $scopes[0]->expects($this->any())->method('getBaseUrl')->willReturn('http://www.example.com');
        $scopes[1] = $this->getMockBuilder(ScopeInterface::class)
            ->getMock();
        $scopes[1]->expects($this->any())->method('getBaseUrl')->willReturn('https://www.example2.com');

        $this->scopeResolver->expects($this->atLeastOnce())->method('getScopes')->willReturn($scopes);

        $this->assertEquals($result, $this->object->isOwnOrigin($url));
    }

    /**
     * @return array
     */
    public static function isOwnOriginDataProvider()
    {
        return [
            ['http://www.example.com/some/page/', true],
            ['http://www.test.com/other/page/', false],
        ];
    }
}
