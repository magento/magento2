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
namespace Magento\Core\Model\Config\Section;

class ReaderPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Config\Section\ReaderPool
     */
    protected $_model;

    /**
     * @var \Magento\Core\Model\Config\Section\Reader\DefaultReader
     */
    protected $_defaultReaderMock;

    /**
     * @var \Magento\Core\Model\Config\Section\Reader\Website
     */
    protected $_websiteReaderMock;

    /**
     * @var \Magento\Core\Model\Config\Section\Reader\Store
     */
    protected $_storeReaderMock;

    protected function setUp()
    {
        $this->_defaultReaderMock = $this->getMock(
            'Magento\Core\Model\Config\Section\Reader\DefaultReader', array(), array(), '', false
        );
        $this->_websiteReaderMock = $this->getMock(
            'Magento\Core\Model\Config\Section\Reader\Website', array(), array(), '', false
        );
        $this->_storeReaderMock = $this->getMock(
            'Magento\Core\Model\Config\Section\Reader\Store', array(), array(), '', false
        );

        $this->_model = new \Magento\Core\Model\Config\Section\ReaderPool(
            $this->_defaultReaderMock,
            $this->_websiteReaderMock,
            $this->_storeReaderMock
        );
    }

    /**
     * @covers \Magento\Core\Model\Config\Section\ReaderPool::getReader
     * @dataProvider getReaderDataProvider
     * @param string $scope
     * @param string $instanceType
     */
    public function testGetReader($scope, $instanceType)
    {
        $this->assertInstanceOf($instanceType, $this->_model->getReader($scope));
    }

    /**
     * @return array
     */
    public function getReaderDataProvider()
    {
        return array(
            array(
                'scope' => 'default',
                'expectedResult' => 'Magento\Core\Model\Config\Section\Reader\DefaultReader'
            ),
            array(
                'scope' => 'website',
                'expectedResult' => 'Magento\Core\Model\Config\Section\Reader\Website'
            ),
            array(
                'scope' => 'websites',
                'expectedResult' => 'Magento\Core\Model\Config\Section\Reader\Website'
            ),
            array(
                'scope' => 'store',
                'expectedResult' => 'Magento\Core\Model\Config\Section\Reader\Store'
            ),
            array(
                'scope' => 'stores',
                'expectedResult' => 'Magento\Core\Model\Config\Section\Reader\Store'
            )
        );
    }
}
