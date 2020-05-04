<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Invoice\Grid\Row;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Grid\Row\UrlGenerator;
use Magento\Sales\Model\Order\Invoice\Grid\Row\UrlGenerator as InvoiceUrlGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UrlGeneratorTest extends TestCase
{
    /**
     * @var UrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlMock;

    /**
     * @var AuthorizationInterface|MockObject
     */
    protected $authorizationMock;

    protected function setUp(): void
    {
        $this->urlMock = $this->getMockForAbstractClass(
            UrlInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->authorizationMock = $this->getMockForAbstractClass(
            AuthorizationInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->urlGenerator = new InvoiceUrlGenerator(
            $this->urlMock,
            $this->authorizationMock,
            [
                'path' => 'path'
            ]
        );
    }

    /**
     * Provides permission for url generation
     *
     * @return array
     */
    public function permissionProvider()
    {
        return [
            [true, null],
            [false, false]
        ];
    }

    /**
     * @param bool $isAllowed
     * @param null|bool $url
     * @dataProvider permissionProvider
     */
    public function testGetUrl($isAllowed, $url)
    {
        $this->authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with('Magento_Sales::sales_invoice', null)
            ->willReturn($isAllowed);
        $this->assertEquals($url, $this->urlGenerator->getUrl(new DataObject()));
    }
}
