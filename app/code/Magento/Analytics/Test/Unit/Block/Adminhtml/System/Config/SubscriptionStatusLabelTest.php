<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Block\Adminhtml\System\Config;

use Magento\Analytics\Block\Adminhtml\System\Config\SubscriptionStatusLabel;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class SignupTest
 */
class SubscriptionStatusLabelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubscriptionStatusLabel
     */
    private $subscriptionStatusLabel;

    /**
     * @var AbstractElement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractElementMock;

    /**
     * @var SubscriptionStatusProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionStatusProviderMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Form|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->subscriptionStatusProviderMock = $this->getMockBuilder(SubscriptionStatusProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->abstractElementMock = $this->getMockBuilder(AbstractElement::class)
            ->setMethods(['getComment'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->formMock = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriptionStatusLabel = new SubscriptionStatusLabel(
            $this->contextMock,
            $this->subscriptionStatusProviderMock
        );
    }

    /**
     * @return void
     */
    public function testRender()
    {
        $this->abstractElementMock->setForm($this->formMock);
        $this->subscriptionStatusProviderMock->expects($this->once())
            ->method('getStatus')
            ->willReturn('Enabled');
        $this->abstractElementMock->expects($this->any())
            ->method('getComment')
            ->willReturn('Subscription status: Enabled');
        $this->assertRegexp(
            "/Subscription status: Enabled/",
            $this->subscriptionStatusLabel->render($this->abstractElementMock)
        );
    }
}
