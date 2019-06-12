<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\GetUtilityPageIdentifiers;
use Magento\Framework\App\Config\ScopeConfigInterface;
<<<<<<< HEAD
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
=======
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for GetUtilityPageIdentifiers model.
 */
class GetUtilityPageIdentifiersTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var GetUtilityPageIdentifiers
     */
    private $model;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
<<<<<<< HEAD
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
=======
    private $scopeConfig;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->model = $objectManager->getObject(
            GetUtilityPageIdentifiers::class,
            [
                'scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    /**
     * Test GetUtilityPageIdentifiers::execute() will read config for getting correct routes.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     *
     * @return void
     */
    public function testExecute()
    {
<<<<<<< HEAD
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
=======
        $cmsHomePage = 'testCmsHomePage';
        $cmsNoRoute = 'testCmsNoRoute';
        $cmsNoCookies = 'testCmsNoCookies';
        $this->scopeConfig->expects($this->exactly(3))
            ->method('getValue')
            ->withConsecutive(
                [$this->identicalTo('web/default/cms_home_page'), $this->identicalTo(ScopeInterface::SCOPE_STORE)],
                [$this->identicalTo('web/default/cms_no_route'), $this->identicalTo(ScopeInterface::SCOPE_STORE)],
                [$this->identicalTo('web/default/cms_no_cookies'), $this->identicalTo(ScopeInterface::SCOPE_STORE)]
            )->willReturnOnConsecutiveCalls(
                $cmsHomePage,
                $cmsNoRoute,
                $cmsNoCookies
            );
        $this->assertSame([$cmsHomePage, $cmsNoRoute, $cmsNoCookies], $this->model->execute());
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }
}
