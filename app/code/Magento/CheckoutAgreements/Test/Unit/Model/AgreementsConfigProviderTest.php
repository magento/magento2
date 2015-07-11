<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Model\AgreementsProvider;

class AgreementsConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Model\AgreementsConfigProvider
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $agreementsRepositoryMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->agreementsRepositoryMock = $this->getMock(
            '\Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface',
            [],
            [],
            '',
            false
        );
        $this->model = $objectManager->getObject(
            'Magento\CheckoutAgreements\Model\AgreementsConfigProvider',
            [
                'scopeConfiguration' => $this->scopeConfigMock,
                'checkoutAgreementsRepository' => $this->agreementsRepositoryMock
            ]
        );
    }

    /**
     * @dataProvider getConfigDataProvider
     * @param bool $isAgreementsEnabled
     * @param array $agreements
     * @param array $expectedResult
     */
    public function testGetConfig($isAgreementsEnabled, $agreements, $expectedResult)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn($isAgreementsEnabled);
        $this->agreementsRepositoryMock->expects($this->any())->method('getList')->willReturn($agreements);
        $this->assertEquals($expectedResult, $this->model->getConfig());
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            [true, ['agreement'], ['checkoutAgreementsEnabled' => true]],
            [true, [], []],
            [false, [], []]
        ];
    }
}
