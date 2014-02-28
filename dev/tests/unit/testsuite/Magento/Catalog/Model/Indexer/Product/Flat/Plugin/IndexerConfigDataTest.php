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
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model\Indexer\Product\Flat\Plugin;

class IndexerConfigDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Plugin\IndexerConfigData
     */
    protected $_model;

    /**
     * @var \Magento\Code\Plugin\InvocationChain|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_chainMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stateMock;

    protected function setUp()
    {
        $this->_stateMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Flat\State', array('isFlatEnabled'), array(), '', false
        );

        $this->_chainMock = $this->getMock(
            'Magento\Code\Plugin\InvocationChain', array('proceed'), array(), '', false
        );

        $this->_model = new \Magento\Catalog\Model\Indexer\Product\Flat\Plugin\IndexerConfigData(
            $this->_stateMock
        );
    }

    /**
     * @param $isFlat
     * @param array $arguments
     * @param $inputData
     * @param $outputData
     * @dataProvider aroundGetDataProvider
     */
    public function testAroundGet($isFlat, array $arguments, $inputData, $outputData)
    {
        $this->_stateMock->expects($this->once())
            ->method('isFlatEnabled')
            ->will($this->returnValue($isFlat));

        $this->_chainMock->expects($this->once())
            ->method('proceed')
            ->with($arguments)
            ->will($this->returnValue($inputData));

        $this->assertEquals($outputData, $this->_model->aroundGet($arguments, $this->_chainMock));
    }

    public function aroundGetDataProvider()
    {
        $flatIndexerData = array(
            'indexer_id' => 'catalog_product_flat',
            'action' => '\Action\Class',
            'title' => 'Title',
            'description' => 'Description',
        );
        $otherIndexerData = array(
            'indexer_id' => 'other_indexer',
            'action' => '\Action\Class',
            'title' => 'Title',
            'description' => 'Description',
        );
        return array(
            // flat is enabled, nothing is being changed
            array(
                true,
                array(),
                array('catalog_product_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData),
                array('catalog_product_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData),
            ),
            // flat is disabled, path is absent, flat indexer is being removed
            array(
                false,
                array(),
                array('catalog_product_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData),
                array('other_indexer' => $otherIndexerData),
            ),
            // flat is disabled, path is null, flat indexer is being removed
            array(
                false,
                array('path' => null),
                array('catalog_product_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData),
                array('other_indexer' => $otherIndexerData),
            ),
            // flat is disabled, path is flat indexer, flat indexer is being removed
            array(
                false,
                array('path' => 'catalog_product_flat'),
                $flatIndexerData,
                null,
            ),
            // flat is disabled, path is flat indexer, default is array(), flat indexer is being array()
            array(
                false,
                array('path' => 'catalog_product_flat', 'default' => array()),
                $flatIndexerData,
                array(),
            ),
            // flat is disabled, path is other indexer, nothing is being changed
            array(
                false,
                array('path' => 'other_indexer'),
                $otherIndexerData,
                $otherIndexerData,
            ),
        );
    }
}
