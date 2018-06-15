<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Observer;

use Magento\Captcha\Helper\Data as CaptchaDataHelper;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CaptchaStringResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var CaptchaStringResolver
     */
    private $captchaStringResolver;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->requestMock = $this->createMock(HttpRequest::class);
        $this->captchaStringResolver = $this->objectManagerHelper->getObject(CaptchaStringResolver::class);
    }

    public function testResolveWithFormIdSet()
    {
        $formId = 'contact_us';
        $captchaValue = 'some-value';

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with(CaptchaDataHelper::INPUT_NAME_FIELD_VALUE)
            ->willReturn([$formId => $captchaValue]);

        self::assertEquals(
            $this->captchaStringResolver->resolve($this->requestMock, $formId),
            $captchaValue
        );
    }

    public function testResolveWithNoFormIdInRequest()
    {
        $formId = 'contact_us';

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with(CaptchaDataHelper::INPUT_NAME_FIELD_VALUE)
            ->willReturn([]);

        self::assertEquals(
            $this->captchaStringResolver->resolve($this->requestMock, $formId),
            ''
        );
    }
}
