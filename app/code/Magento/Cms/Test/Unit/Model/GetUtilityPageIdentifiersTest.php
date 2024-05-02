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
    private GetUtilityPageIdentifiers $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private ScopeConfigInterface|MockObject $scopeConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->model = new GetUtilityPageIdentifiers($this->scopeConfig);
    }

    /**
     * Test GetUtilityPageIdentifiers::execute() will read config for getting correct routes.
     *
     * @dataProvider webDefaultPages
     */
    public function testExecute(array $configValues, array $identifiers): void
    {
        $this->scopeConfig->expects($this->exactly(3))
            ->method('getValue')
            ->withConsecutive(
                [$this->identicalTo('web/default/cms_home_page'), $this->identicalTo(ScopeInterface::SCOPE_STORE)],
                [$this->identicalTo('web/default/cms_no_route'), $this->identicalTo(ScopeInterface::SCOPE_STORE)],
                [$this->identicalTo('web/default/cms_no_cookies'), $this->identicalTo(ScopeInterface::SCOPE_STORE)]
            )->willReturnOnConsecutiveCalls(...$configValues);
        $this->assertSame($identifiers, $this->model->execute());
    }

    public function webDefaultPages(): array
    {
        return [
            [
                ['testCmsHomePage', 'testCmsNoRoute', 'testCmsNoCookies'],
                ['testCmsHomePage', 'testCmsNoRoute', 'testCmsNoCookies']
            ],
            [
                ['testCmsHomePage|1', 'testCmsNoRoute|1', 'testCmsNoCookies|1'],
                ['testCmsHomePage', 'testCmsNoRoute', 'testCmsNoCookies']
            ]
        ];
    }
}
