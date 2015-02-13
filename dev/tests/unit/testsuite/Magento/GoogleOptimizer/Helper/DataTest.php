<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\GoogleOptimizer\Helper;

/**
 * Class DataTest
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_googleAnalyticsHelperMock;

    /**
     * @var \Magento\GoogleOptimizer\Helper\Data
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_googleAnalyticsHelperMock = $this->getMock(
            'Magento\GoogleAnalytics\Helper\Data',
            [],
            [],
            '',
            false
        );

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $context = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->_helper = $objectManagerHelper->getObject(
            'Magento\GoogleOptimizer\Helper\Data',
            [
                'scopeConfig' => $this->_scopeConfigMock,
                'analyticsHelper' => $this->_googleAnalyticsHelperMock,
                'context' => $context
            ]
        );
    }

    /**
     * @param bool $isExperimentsEnabled
     * @dataProvider dataProviderBoolValues
     */
    public function testGoogleExperimentIsEnabled($isExperimentsEnabled)
    {
        $store = 1;
        $this->_scopeConfigMock->expects(
            $this->once()
        )->method(
            'isSetFlag'
        )->with(
            \Magento\GoogleOptimizer\Helper\Data::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        )->will(
            $this->returnValue($isExperimentsEnabled)
        );

        $this->assertEquals($isExperimentsEnabled, $this->_helper->isGoogleExperimentEnabled($store));
    }

    /**
     * @return array
     */
    public function dataProviderBoolValues()
    {
        return [[true], [false]];
    }

    /**
     * @param bool $isExperimentsEnabled
     * @param bool $isAnalyticsAvailable
     * @param bool $result
     * @dataProvider dataProviderForTestGoogleExperimentIsActive
     */
    public function testGoogleExperimentIsActive($isExperimentsEnabled, $isAnalyticsAvailable, $result)
    {
        $store = 1;
        $this->_scopeConfigMock->expects(
            $this->once()
        )->method(
            'isSetFlag'
        )->with(
            \Magento\GoogleOptimizer\Helper\Data::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        )->will(
            $this->returnValue($isExperimentsEnabled)
        );

        $this->_googleAnalyticsHelperMock->expects(
            $this->any()
        )->method(
            'isGoogleAnalyticsAvailable'
        )->with(
            $store
        )->will(
            $this->returnValue($isAnalyticsAvailable)
        );

        $this->assertEquals($result, $this->_helper->isGoogleExperimentActive($store));
    }

    /**
     * @return array
     */
    public function dataProviderForTestGoogleExperimentIsActive()
    {
        return [
            [true, true, true],
            [false, true, false],
            [false, false, false],
            [true, false, false]
        ];
    }
}
