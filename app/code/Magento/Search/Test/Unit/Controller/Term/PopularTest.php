<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Controller\Term;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Forward as ResultForward;
use Magento\Framework\Controller\Result\ForwardFactory as ResultForwardFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;
use Magento\Search\Controller\Term\Popular;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PopularTest extends TestCase
{
    private const XML_PATH_SEO_SEARCH_TERMS = 'catalog/seo/search_terms';

    /**
     * @var Popular
     */
    private $action;

    /**
     * @var ResultForwardFactory|MockObject
     */
    private $resultForwardFactoryMock;

    /**
     * @var ResultPageFactory|MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $this->resultForwardFactoryMock = $this->getMockBuilder(ResultForwardFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactoryMock = $this->getMockBuilder(ResultPageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->action = $objectManager->getObject(
            Popular::class,
            [
                'resultForwardFactory' => $this->resultForwardFactoryMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    public function testResult()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(static::XML_PATH_SEO_SEARCH_TERMS, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $resultPageMock = $this->getMockBuilder(ResultPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $this->assertSame($resultPageMock, $this->action->execute());
    }

    public function testResultWithDisabledPage()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(static::XML_PATH_SEO_SEARCH_TERMS, ScopeInterface::SCOPE_STORE)
            ->willReturn(false);
        $resultForwardMock = $this->getMockBuilder(ResultForward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultForwardMock);
        $resultForwardMock->expects($this->once())
            ->method('forward')
            ->with('noroute');

        $this->assertSame($resultForwardMock, $this->action->execute());
    }
}
