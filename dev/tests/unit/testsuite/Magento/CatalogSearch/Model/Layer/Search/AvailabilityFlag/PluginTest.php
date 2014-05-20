<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\CatalogSearch\Model\Layer\Search\AvailabilityFlag;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $engineProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $engineMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    /**
     * @var \Magento\CatalogSearch\Model\Layer\Search\AvailabilityFlag\Plugin
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Catalog\Model\Layer\AvailabilityFlagInterface');
        $this->layerMock = $this->getMock('\Magento\Catalog\Model\Layer', array(), array(), '', false);
        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->engineMock = $this->getMock('\Magento\CatalogSearch\Model\Resource\EngineInterface');
        $this->collectionMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\Collection',
            array(),
            array(),
            '',
            false
        );
        $this->engineProviderMock = $this->getMock(
            '\Magento\CatalogSearch\Model\Resource\EngineProvider',
            array(),
            array(),
            '',
            false
        );

        $this->engineProviderMock->expects($this->any())->method('get')->will($this->returnValue($this->engineMock));
        $this->layerMock->expects($this->any())->method('getProductCollection')
            ->will($this->returnValue($this->collectionMock));

        $this->model = new Plugin($this->scopeConfigMock, $this->engineProviderMock);
    }

    /**
     * @covers \Magento\CatalogSearch\Model\Layer\Search\AvailabilityFlag\Plugin::aroundIsEnabled
     * @covers \Magento\CatalogSearch\Model\Layer\Search\AvailabilityFlag\Plugin::__construct
     */
    public function testaroundIsEnabledLayeredNavigationIsNotAllowed()
    {
        $this->engineMock->expects($this->once())
            ->method('isLayeredNavigationAllowed')
            ->will($this->returnValue(false));

        $this->scopeConfigMock->expects($this->never())->method('getValue');

        $proceed = function () {
            $this->fail('Proceed should not be called in this scenario');
        };

        $this->assertEquals(
            false,
            $this->model->aroundIsEnabled($this->subjectMock, $proceed, $this->layerMock, array())
        );
    }

    /**
     * @param int $collectionSize
     * @param int $availableResCount
     *
     * @dataProvider aroundIsEnabledDataProvider
     * @covers \Magento\CatalogSearch\Model\Layer\Search\AvailabilityFlag\Plugin::aroundIsEnabled
     */
    public function testaroundIsEnabledLayeredNavigationIsAllowedParentLogic($collectionSize, $availableResCount)
    {
        $this->engineMock->expects($this->once())
            ->method('isLayeredNavigationAllowed')
            ->will($this->returnValue(true));

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Plugin::XML_PATH_DISPLAY_LAYER_COUNT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue($availableResCount));

        $this->collectionMock->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue($collectionSize));

        $proceed = function () {
            return true;
        };

        $this->assertEquals(
            true,
            $this->model->aroundIsEnabled($this->subjectMock, $proceed, $this->layerMock, array())
        );
    }

    /**
     * @return array
     */
    public function aroundIsEnabledDataProvider()
    {
        return array(
            array(
                'collectionSize' => 10,
                'availableResCount' => 15
            ),
            array(
                'collectionSize' => 0,
                'availableResCount' => 10
            ),
        );
    }

    /**
     * @covers \Magento\CatalogSearch\Model\Layer\Search\AvailabilityFlag\Plugin::aroundIsEnabled
     */
    public function testaroundIsEnabledLayeredNavigationIsAllowed()
    {
        $this->engineMock->expects($this->once())
            ->method('isLayeredNavigationAllowed')
            ->will($this->returnValue(true));

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(10));

        $this->collectionMock->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(15));

        $proceed = function () {
            $this->fail('Proceed should not be called in this scenario');
        };

        $this->assertEquals(
            false,
            $this->model->aroundIsEnabled($this->subjectMock, $proceed, $this->layerMock, array())
        );
    }
}
