<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Block\Adminhtml\Js;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Block\Adminhtml\Js\Checker testing
 */
class CheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Security\Block\Adminhtml\Js\Checker
     */
    protected $block;

    /**
     * @var \Magento\Framework\Json\Encoder
     */
    protected $jsonEncoderMock;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->jsonEncoderMock =  $this->getMock(
            '\Magento\Framework\Json\Encoder',
            [],
            [],
            '',
            false
        );

        $this->urlBuilder = $this->getMock(
            '\Magento\Framework\UrlInterface',
            [],
            [],
            '',
            false
        );

        $this->block = $this->objectManager->getObject(
            '\Magento\Security\Block\Adminhtml\Js\Checker',
            [
                'jsonEncoder' => $this->jsonEncoderMock,
                'urlBuilder' => $this->urlBuilder,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetSessionCheckerJson()
    {
        $requestUrl = 'http://host/index.php/security/session/check';
        $redirectUrl = 'http://host/index.php/admin';
        $this->urlBuilder->expects($this->exactly(2))->method('getUrl')->willReturnOnConsecutiveCalls(
            $requestUrl,
            $redirectUrl
        );
        $value = [
            'requestUrl' => $requestUrl,
            'redirectUrl' => $redirectUrl
        ];
        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->with($value)
            ->willReturn(json_encode($value));
        $this->assertEquals(json_encode($value), $this->block->getSessionCheckerJson());
    }
}
