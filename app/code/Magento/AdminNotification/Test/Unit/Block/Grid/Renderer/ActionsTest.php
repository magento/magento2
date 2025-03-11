<?php
declare(strict_types = 1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\AdminNotification\Block\Grid\Renderer\Actions
 */

namespace Magento\AdminNotification\Test\Unit\Block\Grid\Renderer;

use Magento\AdminNotification\Block\Grid\Renderer\Actions;
use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ActionsTest extends TestCase
{
    /**
     * System under Test
     * @var Actions
     */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Escaper|MockObject $escaperMock */
        $escaperMock = $this->createMock(Escaper::class);
        $escaperMock->expects($this->atLeastOnce())->method('escapeUrl')->willReturn('https://magento.com');

        /** @var AuthorizationInterface|MockObject $authorizationMock */
        $authorizationMock = $this->getMockForAbstractClass(AuthorizationInterface::class);
        $authorizationMock->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->willReturn(true);

        /** @var UrlInterface|MockObject $urlBuilder */
        $urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $urlBuilder->expects($this->once())->method('getUrl')->willReturn('http://magento.com');

        /** @var Context|MockObject $contextMock */
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->atLeastOnce())->method('getEscaper')->willReturn($escaperMock);
        $contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($urlBuilder);
        $contextMock->expects($this->once())->method('getAuthorization')->willReturn($authorizationMock);

        /** @var Data|MockObject $urlHelperMock */
        $urlHelperMock = $this->createMock(Data::class);
        $urlHelperMock->expects($this->once())->method('getEncodedUrl')->willReturn('http://magento.com');

        $this->sut = new Actions($contextMock, $urlHelperMock);
    }

    public function testShouldRenderMessageWhenUrlIsGiven() : void
    {
        $dataObject = new DataObject();
        $dataObject->setdata('url', 'https://magento.com');
        $dataObject->setdata('is_read', true);
        $dataObject->setdata('id', 1);

        $actual   = $this->sut->render($dataObject);

        // Ignoring Code Style at this point due to the long HEREDOC
        // phpcs:disable
        $expected = <<<HTML
<a class="action-details" target="_blank" href="https://magento.com">Read Details</a><a class="action-delete" href="https://magento.com" onClick="deleteConfirm('Are you sure?', this.href); return false;">Remove</a>
HTML;
        // phpcs:enable

        $this->assertEquals($actual, $expected);
    }
}
