<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\View;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ObserverTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Observer
     */
    private $observer;

    /**
     * @var MockObject
     */
    private $persistentSessionMock;

    /**
     * @var MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var MockObject
     */
    private $customerViewHelperMock;

    /**
     * @var MockObject
     */
    private $escaperMock;

    /**
     * @var MockObject
     */
    private $layoutMock;

    /**
     * @var MockObject
     */
    private $sessionMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->persistentSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->customerViewHelperMock = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->createMock(LayoutInterface::class);
        $this->sessionMock = $this->createPartialMockWithReflection(
            Session::class,
            ['getCustomerId']
        );
        $this->observer = $objectManagerHelper->getObject(
            Observer::class,
            [
                'persistentSession' => $this->persistentSessionMock,
                'customerRepository' => $this->customerRepositoryMock,
                'customerViewHelper' => $this->customerViewHelperMock,
                'escaper' => $this->escaperMock,
                'layout' => $this->layoutMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testEmulateWelcomeBlock(): void
    {
        $welcomeMessage =  __('&nbsp;');
        $block = $this->createPartialMockWithReflection(
            AbstractBlock::class,
            ['setWelcome']
        );
        $block->expects($this->once())->method('setWelcome')->with($welcomeMessage);

        $this->observer->emulateWelcomeBlock($block);
    }
}
