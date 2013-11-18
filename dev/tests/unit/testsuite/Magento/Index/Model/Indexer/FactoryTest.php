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
namespace Magento\Index\Model\Indexer;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Index\Model\Indexer\Factory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_indexerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager');
        $this->_indexerMock = $this->getMock(
            'Magento\Catalog\Model\Category\Indexer\Flat', array(), array(), '', false
        );
        $this->_model = new \Magento\Index\Model\Indexer\Factory($this->_objectManagerMock);
    }

    /**
     * @covers \Magento\Index\Model\Indexer\Factory::create
     */
    public function testCreate()
    {
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento_Indexer')
            ->will($this->returnValue($this->_indexerMock));

        $this->assertInstanceOf('Magento\Index\Model\Indexer\AbstractIndexer',
            $this->_model->create('Magento_Indexer')
        );
    }

    /**
     * @covers \Magento\Index\Model\Indexer\Factory::create
     */
    public function testCreateWithNoInstance()
    {
        $this->assertEquals(null, $this->_model->create('Magento_Indexer'));
    }
}
