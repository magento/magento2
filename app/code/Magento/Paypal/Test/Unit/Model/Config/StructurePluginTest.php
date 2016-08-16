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
     * @dataProvider beforeAndAfterGetElementByPathPartsNonPaymentDataProvider
     */
    public function testBeforeAndAfterGetElementByPathPartsNonPayment($pathParts, $returnResult)
    {
        $result = $returnResult ? $this->elementConfigStructureMock : null;

        $this->assertEquals(
            [$pathParts],
            $this->plugin->beforeGetElementByPathParts($this->configStructureMock, $pathParts)
        );
        $this->assertSame(
            $result,
            $this->plugin->afterGetElementByPathParts($this->configStructureMock, $result)
        );
    }

    /**
     * @return array
     */
    public function beforeAndAfterGetElementByPathPartsNonPaymentDataProvider()
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
     *
     * @dataProvider beforeAndAfterGetElementByPathPartsDataProvider
     */
    public function testBeforeAndAfterGetElementByPathPartsNoResult($pathParts, $countryCode, $expectedPathParts)
    {
        $this->backendHelperMock->expects(static::once())
            ->method('getConfigurationCountryCode')
            ->willReturn($countryCode);

        $this->assertEquals(
            [$expectedPathParts],
            $this->plugin->beforeGetElementByPathParts($this->configStructureMock, $pathParts)
        );
        $this->assertEquals(
            null,
            $this->plugin->afterGetElementByPathParts($this->configStructureMock, null)
        );
    }

    /**
     * @param array $pathParts
     * @param string $countryCode
     * @param array $expectedPathParts
     *
     * @dataProvider beforeAndAfterGetElementByPathPartsDataProvider
     */
    public function testBeforeAndAfterGetElementByPathParts($pathParts, $countryCode, $expectedPathParts)
    {
        $this->backendHelperMock->expects(static::once())
            ->method('getConfigurationCountryCode')
            ->willReturn($countryCode);

        $this->assertEquals(
            [$expectedPathParts],
            $this->plugin->beforeGetElementByPathParts($this->configStructureMock, $pathParts)
        );
        $this->assertSame(
            $this->elementConfigStructureMock,
            $this->plugin->afterGetElementByPathParts($this->configStructureMock, $this->elementConfigStructureMock)
        );
    }

    /**
     * @return array
     */
    public function beforeAndAfterGetElementByPathPartsDataProvider()
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
}
