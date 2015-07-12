<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Unit\Block;

use Magento\Contact\Block\ContactForm;

class ContactFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Contact\Block\ContactForm
     */
    protected $contactForm;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder('Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();

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
}
