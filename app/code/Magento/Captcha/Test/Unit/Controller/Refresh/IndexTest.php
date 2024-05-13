<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Controller\Refresh;

use Magento\Captcha\Controller\Refresh\Index;
use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Model\CaptchaInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    private const STUB_FORM_ID = 'StubFormId';
    private const STUB_CAPTCHA_SOURCE = '/stub-captcha-source.jpg';

    /** @var MockObject|RequestInterface */
    private $requestMock;

    /** @var MockObject|ResultJsonFactory */
    private $jsonResultFactoryMock;

    /** @var MockObject|ResultJson */
    private $jsonResultMock;

    /** @var MockObject|CaptchaHelper */
    private $captchaHelperMock;

    /** @var MockObject|LayoutInterface */
    private $layoutMock;

    /** @var MockObject|BlockInterface */
    private $blockMock;

    /** @var MockObject|JsonSerializer */
    private $jsonSerializerMock;

    /** @var MockObject|Context */
    private $contextMock;

    /** @var Index */
    private $refreshAction;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->addMethods(['getPost', 'getContent'])
            ->getMockForAbstractClass();
        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->onlyMethods(['createBlock'])
            ->getMockForAbstractClass();
        $this->blockMock = $this->getMockBuilder(BlockInterface::class)
            ->addMethods(['setFormId', 'setIsAjax'])
            ->onlyMethods(['toHtml'])
            ->getMockForAbstractClass();
        $this->jsonResultFactoryMock = $this->createMock(ResultJsonFactory::class);
        $this->jsonResultMock = $this->createMock(ResultJson::class);
        $this->jsonResultFactoryMock->method('create')
            ->willReturn($this->jsonResultMock);
        $this->jsonSerializerMock = $this->createMock(JsonSerializer::class);
        $this->captchaHelperMock = $this->createMock(CaptchaHelper::class);

        $this->contextMock = $this->createMock(Context::class);

        $this->blockMock->method('setIsAjax')
            ->willReturnSelf();

        $this->layoutMock->method('createBlock')
            ->willReturn($this->blockMock);

        $this->refreshAction = new Index(
            $this->contextMock,
            $this->requestMock,
            $this->jsonResultFactoryMock,
            $this->captchaHelperMock,
            $this->layoutMock,
            $this->jsonSerializerMock
        );
    }

    public function testCaptchaGeneratedWhenPostDataContainsFormId()
    {
        // Given
        $this->requestMock->method('getPost')
            ->with('formId')
            ->willReturn(self::STUB_FORM_ID);
        $this->blockMock->method('setFormId')
            ->willReturnSelf();

        // Expect
        $this->requestMock->expects($this->never())
            ->method('getContent');
        $this->captchaHelperMock->expects($this->once())
            ->method('getCaptcha')
            ->with(self::STUB_FORM_ID)
            ->willReturn(
                $this->getCaptchaModelMock(self::STUB_CAPTCHA_SOURCE)
            );

        // When
        $this->refreshAction->execute();
    }

    public function testCaptchaFallsBackToRequestContentIfPostMissing()
    {
        // Given
        $this->requestMock->method('getPost')
            ->with('formId')
            ->willReturn(null);
        $this->blockMock->method('setFormId')
            ->willReturnSelf();

        // Expect
        $this->requestMock->expects(self::once())
            ->method('getContent')
            ->willReturn(null);
        $this->captchaHelperMock->expects($this->once())
            ->method('getCaptcha')
            ->with(null)
            ->willReturn(
                $this->getCaptchaModelMock(self::STUB_CAPTCHA_SOURCE)
            );

        // When
        $this->refreshAction->execute();
    }

    /**
     * @param string $imageSource
     * @return MockObject|CaptchaInterface
     */
    private function getCaptchaModelMock(string $imageSource): CaptchaInterface
    {
        $modelMock = $this->getMockBuilder(CaptchaInterface::class)
            ->onlyMethods(['generate', 'getBlockName'])
            ->addMethods(['getImgSrc'])
            ->getMockForAbstractClass();

        $modelMock->method('getImgSrc')
            ->willReturn($imageSource);

        return $modelMock;
    }
}
