<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Grid\Row;

class UrlGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Grid\Row\UrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var \Magento\Backend\Model\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlMock;

    /**
     * @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorizationMock;

    protected function setUp()
    {
        $this->urlMock = $this->getMockForAbstractClass(
            'Magento\Backend\Model\UrlInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->authorizationMock = $this->getMockForAbstractClass(
            'Magento\Framework\AuthorizationInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->urlGenerator = new \Magento\Sales\Model\Order\Grid\Row\UrlGenerator(
            $this->urlMock,
            $this->authorizationMock,
            ['path' => 'path']
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
            ->with('Magento_Sales::actions_view', null)
            ->willReturn($isAllowed);
        $this->assertEquals($url, $this->urlGenerator->getUrl(new \Magento\Framework\DataObject()));
    }
}
