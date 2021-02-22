<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Unit\Block;

use Magento\Contact\Block\ContactForm;

class ContactFormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Contact\Block\ContactForm
     */
    protected $contactForm;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlBuilderMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrlBuilder'])
            ->getMock();

        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
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
