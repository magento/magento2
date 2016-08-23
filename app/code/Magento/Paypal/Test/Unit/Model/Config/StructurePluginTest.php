<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Config;

use Magento\Paypal\Model\Config\StructurePlugin as ConfigStructurePlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Config\Model\Config\ScopeDefiner as ConfigScopeDefiner;
use Magento\Paypal\Helper\Backend as BackendHelper;
use Magento\Config\Model\Config\Structure as ConfigStructure;
use Magento\Config\Model\Config\Structure\ElementInterface as ElementConfigStructure;

class StructurePluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigStructurePlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ConfigScopeDefiner|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configScopeDefinerMock;

    /**
     * @var BackendHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backendHelperMock;

    /**
     * @var ConfigStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configStructureMock;

    /**
     * @var ElementConfigStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $elementConfigStructureMock;

    protected function setUp()
    {
        $this->configScopeDefinerMock = $this->getMockBuilder(ConfigScopeDefiner::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendHelperMock = $this->getMockBuilder(BackendHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configStructureMock = $this->getMockBuilder(ConfigStructure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->elementConfigStructureMock = $this->getMockBuilder(ElementConfigStructure::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            ConfigStructurePlugin::class,
            ['scopeDefiner' => $this->configScopeDefinerMock, 'helper' => $this->backendHelperMock]
        );
    }

    public function testGetPaypalConfigCountriesWithOther()
    {
        $countries = ConfigStructurePlugin::getPaypalConfigCountries(true);

        $this->assertContains('payment_us', $countries);
        $this->assertContains('payment_other', $countries);
    }

    public function testGetPaypalConfigCountries()
    {
        $countries = ConfigStructurePlugin::getPaypalConfigCountries(false);

        $this->assertContains('payment_us', $countries);
        $this->assertNotContains('payment_other', $countries);
    }

    /**
     * @param array $pathParts
     * @param bool $returnResult
     *
     * @dataProvider aroundGetElementByPathPartsNonPaymentDataProvider
     */
    public function testAroundGetElementByPathPartsNonPayment($pathParts, $returnResult)
    {
        $result = $returnResult ? $this->elementConfigStructureMock : null;
        $proceed = function () use ($result) {
            return $result;
        };

        $this->assertSame(
            $result,
            $this->plugin->aroundGetElementByPathParts($this->configStructureMock, $proceed, $pathParts)
        );
    }

    /**
     * @return array
     */
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
     *
     * @dataProvider aroundGetElementByPathPartsDataProvider
     */
    public function testAroundGetElementByPathPartsNoResult($pathParts, $countryCode)
    {
        $proceed = function () {
            return null;
        };

        $this->backendHelperMock->expects(static::once())
            ->method('getConfigurationCountryCode')
            ->willReturn($countryCode);

        $this->assertEquals(
            null,
            $this->plugin->aroundGetElementByPathParts($this->configStructureMock, $proceed, $pathParts)
        );
    }

    /**
     * @param array $pathParts
     * @param string $countryCode
     *
     * @dataProvider aroundGetElementByPathPartsDataProvider
     */
    public function testAroundGetElementByPathParts($pathParts, $countryCode)
    {
        $result = $this->elementConfigStructureMock;
        $proceed = function () use ($result) {
            return $result;
        };

        $this->backendHelperMock->expects(static::once())
            ->method('getConfigurationCountryCode')
            ->willReturn($countryCode);

        $this->assertSame(
            $this->elementConfigStructureMock,
            $this->plugin->aroundGetElementByPathParts($this->configStructureMock, $proceed, $pathParts)
        );
    }

    /**
     * @return array
     */
    public function aroundGetElementByPathPartsDataProvider()
    {
        return [
            [
                ['payment', 'group1', 'group2', 'field'],
                'any',
            ],
            [
                ['payment', 'group1', 'group2', 'field'],
                'DE',
            ]
        ];
    }
}
