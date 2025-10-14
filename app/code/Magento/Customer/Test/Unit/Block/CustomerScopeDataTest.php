<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block;

use Magento\Customer\Block\CustomerScopeData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerScopeDataTest extends TestCase
{
    /** @var CustomerScopeData */
    private $model;

    /** @var Context|MockObject */
    private $contextMock;

    /** @var StoreManagerInterface|MockObject */
    private $storeManagerMock;

    /** @var ScopeConfigInterface|MockObject */
    private $scopeConfigMock;

    /** @var EncoderInterface|MockObject */
    private $encoderMock;

    /** @var Json|MockObject */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();

        $this->encoderMock = $this->getMockBuilder(EncoderInterface::class)
            ->getMock();

        $this->serializerMock = $this->getMockBuilder(Json::class)
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
            ->onlyMethods(['getWebsiteId'])
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

    public function testEncodeConfiguration()
    {
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

        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->with($rules)
            ->willReturn(json_encode($rules));

        $this->assertEquals(
            json_encode($rules),
            $this->model->encodeConfiguration($rules)
        );
    }
}
