<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\GoogleOptimizer\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleOptimizer\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class DataTest extends TestCase
{
    /**
     * Xml path google experiments enabled
     */
    private const XML_PATH_ENABLED = 'google/analytics/experiments';

    /**
     * Xml path google experiments enabled for GA4
     */
    private const XML_PATH_ENABLED_GA4 = 'google/gtag/analytics4/experiments';

    /**
     * @var MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var MockObject
     */
    protected $_googleAnalyticsHelperMock;

    /**
     * @var Data
     */
    protected $_helper;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $className = Data::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var Context $context */
        $context = $arguments['context'];
        $this->_scopeConfigMock = $context->getScopeConfig();
        $this->_googleAnalyticsHelperMock = $arguments['analyticsHelper'];
        $this->_helper = $objectManagerHelper->getObject($className, $arguments);
    }

    /**
     * @param bool $isExperimentsEnabled
     * @dataProvider dataProviderBoolValues
     */
    public function testGoogleExperimentIsEnabled($isExperimentsEnabled)
    {
        $store = 1;
        $this->_scopeConfigMock->expects(
            $this->any()
        )->method(
            'isSetFlag'
        )->withConsecutive(
            [
                self::XML_PATH_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $store
            ],
            [
                self::XML_PATH_ENABLED_GA4,
                ScopeInterface::SCOPE_STORE,
                $store
            ]
        )->willReturn(
            $isExperimentsEnabled
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
            $this->any()
        )->method(
            'isSetFlag'
        )->withConsecutive(
            [
                self::XML_PATH_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $store
            ],
            [
                self::XML_PATH_ENABLED_GA4,
                ScopeInterface::SCOPE_STORE,
                $store
            ]
        )->willReturn(
            $isExperimentsEnabled
        );

        $this->_googleAnalyticsHelperMock->expects(
            $this->any()
        )->method(
            'isGoogleAnalyticsAvailable'
        )->with(
            $store
        )->willReturn(
            $isAnalyticsAvailable
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
