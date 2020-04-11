<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Unit\Block;

use Magento\Contact\Block\ContactForm;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContactFormTest extends TestCase
{
    /**
     * @var ContactForm
     */
    protected $contactForm;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrlBuilder'])
            ->getMock();

        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->contactForm = new ContactForm(
            $this->contextMock
        );
    }

    /**
     * @return void
     */
    public function testScope()
    {
        $this->assertTrue($this->contactForm->isScopePrivate());
    }

    /**
     * @return void
     */
    public function testGetFormAction()
    {
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('contact/index/post', ['_secure' => true]);
        $this->contactForm->getFormAction();
    }
}
