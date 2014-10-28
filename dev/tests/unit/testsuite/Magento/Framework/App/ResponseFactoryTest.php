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
namespace Magento\Framework\App;

class ResponseFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_expectedObject;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $this->_model = new \Magento\Framework\App\ResponseFactory($this->_objectManagerMock);
    }

    public function testCreate()
    {
        $this->_expectedObject = $this->getMockBuilder('\Magento\Framework\App\ResponseInterface')->getMock();
        $arguments = array(array('property' => 'value'));
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\App\ResponseInterface',
            $arguments
        )->will(
            $this->returnValue($this->_expectedObject)
        );

        $this->assertEquals($this->_expectedObject, $this->_model->create($arguments));
    }
}
