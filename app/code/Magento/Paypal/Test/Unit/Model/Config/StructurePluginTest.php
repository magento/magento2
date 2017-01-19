<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Model\Config;

use Magento\Paypal\Model\Config\Structure\PaymentSectionModifier;
use Magento\Paypal\Model\Config\StructurePlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class StructurePluginTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Paypal\Model\Config\StructurePlugin */
    protected $_model;

    /** @var \Magento\Config\Model\Config\ScopeDefiner|\PHPUnit_Framework_MockObject_MockObject */
    protected $_scopeDefiner;

    /** @var \Magento\Paypal\Helper\Backend|\PHPUnit_Framework_MockObject_MockObject */
    protected $_helper;

    /**
     * @var PaymentSectionModifier
     */
    private $paymentSectionModifier;

    protected function setUp()
    {
        $this->_scopeDefiner = $this->getMock('Magento\Config\Model\Config\ScopeDefiner', [], [], '', false);
        $this->_helper = $this->getMock('Magento\Paypal\Helper\Backend', [], [], '', false);

        $this->paymentSectionModifier = $this->getMockBuilder(PaymentSectionModifier::class)->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->_model = $objectManagerHelper->getObject(
            'Magento\Paypal\Model\Config\StructurePlugin',
            [
                'scopeDefiner' => $this->_scopeDefiner,
                'helper' => $this->_helper,
                'paymentSectionModifier' => $this->paymentSectionModifier,
            ]
        );
    }

    public function testGetPaypalConfigCountriesWithOther()
    {
        $countries = StructurePlugin::getPaypalConfigCountries(true);
        $this->assertContains('payment_us', $countries);
        $this->assertContains('payment_other', $countries);
    }

    public function testGetPaypalConfigCountries()
    {
        $countries = StructurePlugin::getPaypalConfigCountries(false);
        $this->assertContains('payment_us', $countries);
        $this->assertNotContains('payment_other', $countries);
    }

    /**
     * @param array $pathParts
     * @param bool $returnResult
     * @dataProvider aroundGetElementByPathPartsNonPaymentDataProvider
     */
    public function testAroundGetElementByPathPartsNonPayment($pathParts, $returnResult)
    {
        $result = $returnResult
            ? $this->getMockForAbstractClass('Magento\Config\Model\Config\Structure\ElementInterface')
            : null;
        $this->_aroundGetElementByPathPartsAssertResult(
            $result,
            $this->_getElementByPathPartsCallback($pathParts, $result),
            $pathParts
        );
    }

    public function aroundGetElementByPathPartsNonPaymentDataProvider()
    {
        return [
            [['non-payment', 'group1', 'group2', 'field'], true],
            [['non-payment'], true],
            [['non-payment', 'group1', 'group2', 'field'], false],
            [['non-payment'], false],
        ];
    }

    /**
     * @param array $pathParts
     * @param string $countryCode
     * @param array $expectedPathParts
     * @dataProvider aroundGetElementByPathPartsDataProvider
     */
    public function testAroundGetElementByPathPartsNoResult($pathParts, $countryCode, $expectedPathParts)
    {
        $this->_getElementByPathPartsPrepareHelper($countryCode);
        $this->_aroundGetElementByPathPartsAssertResult(
            null,
            $this->_getElementByPathPartsCallback($expectedPathParts, null),
            $pathParts
        );
    }

    /**
     * @param array $pathParts
     * @param string $countryCode
     * @param array $expectedPathParts
     * @dataProvider aroundGetElementByPathPartsDataProvider
     */
    public function testAroundGetElementByPathParts($pathParts, $countryCode, $expectedPathParts)
    {
        $this->_getElementByPathPartsPrepareHelper($countryCode);
        $result = $this->getMockForAbstractClass('Magento\Config\Model\Config\Structure\ElementInterface');
        $this->_aroundGetElementByPathPartsAssertResult(
            $result,
            $this->_getElementByPathPartsCallback($expectedPathParts, $result),
            $pathParts
        );
    }

    public function aroundGetElementByPathPartsDataProvider()
    {
        return [
            [
                ['payment', 'group1', 'group2', 'field'],
                'any',
                ['payment_other', 'group1', 'group2', 'field'],
            ],
            [
                ['payment', 'group1', 'group2', 'field'],
                'DE',
                ['payment_de', 'group1', 'group2', 'field']
            ],
        ];
    }

    /**
     * @param array $pathParts
     * @param string $countryCode
     * @param array $expectedPathParts
     * @dataProvider aroundGetSectionByPathPartsDataProvider
     */
    public function testAroundGetSectionByPathParts($pathParts, $countryCode, $expectedPathParts)
    {
        $this->_getElementByPathPartsPrepareHelper($countryCode);
        $result = $this->getMock('Magento\Config\Model\Config\Structure\Element\Section', [], [], '', false);
        $self = $this;
        $getElementByPathParts = function ($pathParts) use ($self, $expectedPathParts, $result) {
            $self->assertEquals($expectedPathParts, $pathParts);
            $scope = 'any scope';
            $sectionMap = [
                'account' => [],
                'recommended_solutions' => [],
                'other_paypal_payment_solutions' => [],
                'other_payment_methods' => []
            ];
            $self->_scopeDefiner->expects($self->any())
                ->method('getScope')
                ->will($self->returnValue($scope));
            $this->paymentSectionModifier->method('modify')->willReturn($sectionMap);
            $result->expects($self->at(0))
                ->method('getData')
                ->will($self->returnValue(['children' => []]));
            $result->expects($self->at(2))
                ->method('getData')
                ->will($self->returnValue(['children' => $sectionMap]));
            $result->expects($self->at(1))
                ->method('setData')
                ->with(['children' => $sectionMap], $scope)
                ->will($self->returnSelf());
            $result->expects($self->at(3))
                ->method('setData')
                ->with(['children' => $sectionMap,
                    'showInDefault' => true,
                    'showInWebsite' => true,
                    'showInStore' => true], $scope)
                ->will($self->returnSelf());
            return $result;
        };
        $this->_aroundGetElementByPathPartsAssertResult($result, $getElementByPathParts, $pathParts);
    }

    public function aroundGetSectionByPathPartsDataProvider()
    {
        return [
            [['payment'], 'GB', ['payment_gb']],
            [['payment'], 'any', ['payment_other']],
        ];
    }

    /**
     * Assert result of aroundGetElementByPathParts method
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|null $result
     * @param \Closure $getElementByPathParts
     * @param array $pathParts
     */
    private function _aroundGetElementByPathPartsAssertResult($result, $getElementByPathParts, $pathParts)
    {
        $this->assertEquals($result, $this->_model->aroundGetElementByPathParts(
            $this->getMock('Magento\Config\Model\Config\Structure', [], [], '', false),
            $getElementByPathParts,
            $pathParts
        ));
    }

    /**
     * Get callback for aroundGetElementByPathParts method
     *
     * @param array $expectedPathParts
     * @param \PHPUnit_Framework_MockObject_MockObject|null $result
     * @return \Closure
     */
    private function _getElementByPathPartsCallback($expectedPathParts, $result)
    {
        $self = $this;
        return function ($pathParts) use ($self, $expectedPathParts, $result) {
            $self->assertEquals($expectedPathParts, $pathParts);
            return $result;
        };
    }

    /**
     * Prepare helper for test
     *
     * @param string $countryCode
     */
    private function _getElementByPathPartsPrepareHelper($countryCode)
    {
        $this->_helper->expects($this->once())
            ->method('getConfigurationCountryCode')
            ->will($this->returnValue($countryCode));
    }
}
