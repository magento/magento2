<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Helper;

use Magento\Downloadable\Helper\Data;
use Magento\Downloadable\Model\Link;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->method('getScopeConfig')->willReturn($this->scopeConfigMock);

        $objectManager = new ObjectManagerHelper($this);
        $this->helper = $objectManager->getObject(
            Data::class,
            ['context' => $this->contextMock]
        );
    }

    /**
     * Test getIsShareable() with data provider
     *
     * @param int $linkShareable
     * @param bool $config
     * @param bool $expectedResult
     * @dataProvider getIsShareableDataProvider
     */
    public function testGetIsShareable($linkShareable, $config, $expectedResult)
    {
        $this->scopeConfigMock->method('isSetFlag')
            ->with(Link::XML_PATH_CONFIG_IS_SHAREABLE, ScopeInterface::SCOPE_STORE)
            ->willReturn($config);

        $linkMock = $this->createMock(Link::class);
        $linkMock->method('getIsShareable')->willReturn($linkShareable);

        $this->assertEquals($expectedResult, $this->helper->getIsShareable($linkMock));
    }

    /**
     * Data provider for getIsShareable()
     *
     * @return array
     */
    public function getIsShareableDataProvider()
    {
        return [
            'link shareable yes' => [Link::LINK_SHAREABLE_YES, true, true],
            'link shareable no' => [Link::LINK_SHAREABLE_NO, true, false],
            'link shareable config true' => [Link::LINK_SHAREABLE_CONFIG, true, true],
            'link shareable config false' => [Link::LINK_SHAREABLE_CONFIG, false, false],
        ];
    }
}
