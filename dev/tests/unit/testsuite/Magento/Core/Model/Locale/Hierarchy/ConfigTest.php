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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Locale\Hierarchy;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Locale\Hierarchy\Config
     */
    protected $_model;

    /**
     * @var \Magento\Core\Model\Locale\Hierarchy\Config\Reader
     */
    protected $_configReaderMock;

    /**
     * @var \Magento\Config\CacheInterface
     */
    protected $_cacheMock;

    /**
     * @var string
     */
    protected $_cacheId;

    /**
     * @var array
     */
    protected $_testData;

    protected function setUp()
    {
        $this->_configReaderMock = $this->getMock(
            'Magento\Core\Model\Locale\Hierarchy\Config\Reader', array(), array(), '', false
        );
        $this->_cacheMock = $this->getMock('Magento\Config\CacheInterface');
        $this->_cacheId = 'customCacheId';

        $this->_testData = array('key' => 'value');

        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with($this->_cacheId)
            ->will($this->returnValue(serialize($this->_testData)));

        $this->_model = new \Magento\Core\Model\Locale\Hierarchy\Config(
            $this->_configReaderMock,
            $this->_cacheMock,
            $this->_cacheId
        );
    }

    public function testGetHierarchy()
    {
        $this->assertEquals($this->_testData, $this->_model->getHierarchy());
    }
}
