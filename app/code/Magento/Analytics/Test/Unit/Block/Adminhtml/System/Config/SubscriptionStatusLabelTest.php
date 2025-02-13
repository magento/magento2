<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Block\Adminhtml\System\Config;

use Magento\Analytics\Block\Adminhtml\System\Config\SubscriptionStatusLabel;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubscriptionStatusLabelTest extends TestCase
{
    /**
     * @var SubscriptionStatusLabel
     */
    private $subscriptionStatusLabel;

    /**
     * @var AbstractElement|MockObject
     */
    private $abstractElementMock;

    /**
     * @var SubscriptionStatusProvider|MockObject
     */
    private $subscriptionStatusProviderMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Form|MockObject
     */
    private $formMock;

    protected function setUp(): void
    {
        $this->subscriptionStatusProviderMock = $this->createMock(SubscriptionStatusProvider::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->abstractElementMock = $this->getMockBuilder(AbstractElement::class)
            ->addMethods(['getComment'])
            ->onlyMethods(['getElementHtml'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $escaper = $objectManager->getObject(Escaper::class);
        $reflection = new \ReflectionClass($this->abstractElementMock);
        $reflection_property = $reflection->getProperty('_escaper');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->abstractElementMock, $escaper);

        $this->formMock = $this->createMock(Form::class);

        $objectManager = new ObjectManager($this);
        $this->subscriptionStatusLabel = $objectManager->getObject(
            SubscriptionStatusLabel::class,
            [
                'context' => $this->contextMock,
                'subscriptionStatusProvider' => $this->subscriptionStatusProviderMock
            ]
        );
    }

    public function testRender()
    {
        $this->abstractElementMock->setForm($this->formMock);
        $this->subscriptionStatusProviderMock->expects($this->once())
            ->method('getStatus')
            ->willReturn('Enabled');
        $this->abstractElementMock
            ->method('getComment')
            ->willReturn('Subscription status: Enabled');
        $this->assertMatchesRegularExpression(
            "/Subscription status: Enabled/",
            $this->subscriptionStatusLabel->render($this->abstractElementMock)
        );
    }
}
