<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Block\CustomerScopeData;

class CustomerScopeDataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Block\CustomerScopeData */
    private $model;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    private $contextMock;

    /** @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $storeManagerMock;

    /** @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $scopeConfigMock;

    /** @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $encoderMock;

    /** @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject */
    private $serializerMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();

        $this->encoderMock = $this->getMockBuilder(\Magento\Framework\Json\EncoderInterface::class)
            ->getMock();

        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->getMock();

        $this->contextMock->expects($this->exactly(2))
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);

        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->model = new CustomerScopeData(
            $this->contextMock,
            $this->encoderMock,
            [],
            $this->serializerMock
        );
    }

    public function testGetWebsiteId()
    {
        $storeId = 1;

        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getWebsiteId'])
            ->getMockForAbstractClass();

        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($storeId);

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(null)
            ->willReturn($storeMock);

        $this->assertEquals($storeId, $this->model->getWebsiteId());
    }

    public function testGetInvalidationRules()
    {
        $storeId = 1;

        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getWebsiteId'])
            ->getMockForAbstractClass();

        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($storeId);

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(null)
            ->willReturn($storeMock);

        $this->assertEquals(
            [
                '*' => [
                    'Magento_Customer/js/invalidation-processor' => [
                        'invalidationRules' => [
                            'website-rule' => [
                                'Magento_Customer/js/invalidation-rules/website-rule' => [
                                    'scopeConfig' => [
                                        'websiteId' => 1,
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            $this->model->getInvalidationRules()
        );
    }

    public function testGetSerializedInvalidationRules()
    {
        $storeId = 1;
        $rules = [
            '*' => [
                'Magento_Customer/js/invalidation-processor' => [
                    'invalidationRules' => [
                        'website-rule' => [
                            'Magento_Customer/js/invalidation-rules/website-rule' => [
                                'scopeConfig' => [
                                    'websiteId' => 1,
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getWebsiteId'])
            ->getMockForAbstractClass();

        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($storeId);

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(null)
            ->willReturn($storeMock);

        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->with($rules)
            ->willReturn(json_encode($rules));

        $this->assertEquals(
            json_encode($rules),
            $this->model->getSerializedInvalidationRules()
        );
    }
}
