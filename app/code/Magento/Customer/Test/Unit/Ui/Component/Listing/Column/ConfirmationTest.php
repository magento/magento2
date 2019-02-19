<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Ui\Component\Listing\Column\Confirmation;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;

class ConfirmationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Confirmation
     */
    protected $confirmation;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiComponentFactory;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processor;

    /**
     * @var AccountConfirmation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountConfirmation;

    public function setup()
    {
        $this->processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();

        $this->context->expects($this->never())
            ->method('getProcessor')
            ->willReturn($this->processor);

        $this->uiComponentFactory = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->accountConfirmation = $this->createMock(AccountConfirmation::class);

        $this->confirmation = new Confirmation(
            $this->context,
            $this->uiComponentFactory,
            $this->scopeConfig,
            [],
            [],
            $this->accountConfirmation
        );
    }

    /**
     * @param int $isConfirmationRequired
     * @param string|null $confirmation
     * @param \Magento\Framework\Phrase $expected
     * @dataProvider dataProviderPrepareDataSource
     */
    public function testPrepareDataSource(
        $isConfirmationRequired,
        $confirmation,
        $expected
    ) {
        $websiteId = 1;
        $customerId = 1;
        $customerEmail = 'customer@example.com';

        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'id_field_name' => 'entity_id',
                        'entity_id' => $customerId,
                        'confirmation' => $confirmation,
                        'email' => $customerEmail,
                        'website_id' => [
                            $websiteId,
                        ],
                    ],
                ],
            ],
        ];

        $this->processor->expects($this->any())
            ->method('register')
            ->with($this->confirmation)
            ->willReturnSelf();

        $this->accountConfirmation->expects($this->once())
            ->method('isConfirmationRequired')
            ->with($websiteId, $customerId, $customerEmail)
            ->willReturn($isConfirmationRequired);
        
        $this->confirmation->setData('name', 'confirmation');
        $result = $this->confirmation->prepareDataSource($dataSource);

        $this->assertEquals($result['data']['items'][0]['confirmation'], $expected);
    }

    /**
     * @return array
     */
    public function dataProviderPrepareDataSource()
    {
        return [
            [false, 'confirmation', __('Confirmation Not Required')],
            [true, 'confirmation', __('Confirmation Required')],
            [true, null, __('Confirmed')],
        ];
    }
}
