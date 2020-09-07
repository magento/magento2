<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\GetUtilityPageIdentifiers;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
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

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
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
     *
     * @return void
     */
    public function testExecute()
    {
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
    }
}
