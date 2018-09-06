<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\GetUtilityPageIdentifiers;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @covers \Magento\Cms\Model\GetUtilityPageIdentifiers
 */
class GetUtilityPageIdentifiersTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Testable Object
     *
     * @var GetUtilityPageIdentifiers
     */
    private $getUtilityPageIdentifiers;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->getUtilityPageIdentifiers = new GetUtilityPageIdentifiers($this->scopeConfigMock);
    }

    /**
     * Test execute method
     *
     * @return void
     */
    public function testExecute()
    {
        $homePageIdentifier = 'home';
        $noRouteIdentifier = 'no_route';
        $noCookieIdentifier = 'no_cookie';

        $this->scopeConfigMock->expects($this->exactly(3))->method('getValue')->willReturnMap([
            ['web/default/cms_home_page', ScopeInterface::SCOPE_STORE, null, $homePageIdentifier],
            ['web/default/cms_no_route', ScopeInterface::SCOPE_STORE, null, $noRouteIdentifier],
            ['web/default/cms_no_cookies', ScopeInterface::SCOPE_STORE, null, $noCookieIdentifier],
        ]);

        $expected = [$homePageIdentifier, $noRouteIdentifier, $noCookieIdentifier];
        $actual = $this->getUtilityPageIdentifiers->execute();
        self::assertEquals($expected, $actual);
    }
}
