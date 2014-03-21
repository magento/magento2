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
namespace Magento\Index\Model\Indexer;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Index\Model\Indexer\Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    protected function setUp()
    {
        $this->_readerMock = $this->getMock('Magento\Index\Model\Indexer\Config\Reader', array(), array(), '', false);
        $this->_configScopeMock = $this->getMock('Magento\Config\ScopeInterface');
        $this->_cacheMock = $this->getMock('Magento\Config\CacheInterface');
        $this->_model = new \Magento\Index\Model\Indexer\Config(
            $this->_readerMock,
            $this->_configScopeMock,
            $this->_cacheMock
        );
    }

    /**
     * @covers \Magento\Index\Model\Indexer\Config::getIndexer
     */
    public function testGetIndexer()
    {
        $indexerConfig = array('indexerName' => 'indexerConfig');
        $this->_configScopeMock->expects($this->once())->method('getCurrentScope')->will($this->returnValue('global'));
        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'global::indexerConfigCache'
        )->will(
            $this->returnValue(serialize($indexerConfig))
        );
        $this->assertEquals('indexerConfig', $this->_model->getIndexer('indexerName'));
    }

    /**
     * @covers \Magento\Index\Model\Indexer\Config::getAll
     */
    public function testGetAll()
    {
        $indexerConfig = array('indexerName' => 'indexerConfig');
        $this->_configScopeMock->expects($this->once())->method('getCurrentScope')->will($this->returnValue('global'));
        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'global::indexerConfigCache'
        )->will(
            $this->returnValue(serialize($indexerConfig))
        );
        $this->assertEquals($indexerConfig, $this->_model->getAll());
    }
}
