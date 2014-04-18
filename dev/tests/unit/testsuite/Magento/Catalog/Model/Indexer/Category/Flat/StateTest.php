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
namespace Magento\Catalog\Model\Indexer\Category\Flat;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Indexer\Model\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flatIndexerMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->flatIndexerMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\IndexerInterface',
            array(),
            '',
            false,
            false,
            true,
            array('getId', 'getState', '__wakeup')
        );
    }

    public function testIsFlatEnabled()
    {
        $this->scopeConfigMock->expects(
            $this->once()
        )->method(
            'isSetFlag'
        )->with(
            'catalog/frontend/flat_catalog_category'
        )->will(
            $this->returnValue(true)
        );

        $this->model = new \Magento\Catalog\Model\Indexer\Category\Flat\State(
            $this->scopeConfigMock,
            $this->flatIndexerMock
        );
        $this->assertEquals(true, $this->model->isFlatEnabled());
    }

    /**
     * @param $isAvailable
     * @param $isFlatEnabled
     * @param $isValid
     * @param $result
     * @dataProvider isAvailableDataProvider
     */
    public function testIsAvailable($isAvailable, $isFlatEnabled, $isValid, $result)
    {
        $this->flatIndexerMock->expects($this->any())->method('getId')->will($this->returnValue(null));
        $this->flatIndexerMock->expects($this->any())->method('load')->with('catalog_category_flat');
        $this->flatIndexerMock->expects($this->any())->method('isValid')->will($this->returnValue($isValid));

        $this->scopeConfigMock->expects(
            $this->any()
        )->method(
            'isSetFlag'
        )->with(
            'catalog/frontend/flat_catalog_category'
        )->will(
            $this->returnValue($isFlatEnabled)
        );

        $this->model = new \Magento\Catalog\Model\Indexer\Category\Flat\State(
            $this->scopeConfigMock,
            $this->flatIndexerMock,
            $isAvailable
        );
        $this->assertEquals($result, $this->model->isAvailable());
    }

    public function isAvailableDataProvider()
    {
        return array(
            array(false, true, true, false),
            array(true, false, true, false),
            array(true, true, false, false),
            array(true, true, true, true)
        );
    }
}
