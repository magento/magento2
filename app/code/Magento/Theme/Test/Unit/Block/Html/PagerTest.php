<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Html;

use Magento\Framework\App\Config;
use Magento\Framework\Data\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use PHPUnit\Framework\TestCase;

/**
 * Test For Page class
 */
class PagerTest extends TestCase
{

    /**
     * @var Pager $pager
     */
    private $pager;

    /**
     * @var Context $context
     */
    private $context;

    /**
     * @var Config $scopeConfig
     */
    private $scopeConfig;

    /**
     * @var UrlInterface $urlBuilderMock
     */
    private $urlBuilderMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->context->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);
        $this->scopeConfig = $this->createMock(Config::class);
        $this->pager = (new ObjectManager($this))->getObject(
            Pager::class,
            ['context' => $this->context]
        );
    }

    /**
     * Verify current page Url
     *
     * @return void
     */
    public function testGetPageUrl(): void
    {
        $expectedPageUrl = 'page-url';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($expectedPageUrl);
        $this->assertEquals($expectedPageUrl, $this->pager->getPageUrl(0));
    }

    /**
     * Verify get pages method.
     *
     * @return void
     */
    public function testGetPages(): void
    {
        $expectedPages = range(1, 5);
        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->expects($this->exactly(2))
            ->method('getCurPage')
            ->willReturn(2);
        $collectionMock->expects($this->any())
            ->method('getLastPageNumber')
            ->willReturn(10);
        $this->setCollectionProperty($collectionMock);
        $this->assertEquals($expectedPages, $this->pager->getPages());
    }

    /**
     * Set Collection
     *
     * @return void
     */
    private function setCollectionProperty($collection): void
    {
        $reflection = new \ReflectionClass($this->pager);
        $reflection_property = $reflection->getProperty('_collection');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->pager, $collection);
    }
}
