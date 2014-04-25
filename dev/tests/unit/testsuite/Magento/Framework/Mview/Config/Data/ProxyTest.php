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
namespace Magento\Framework\Mview\Config\Data;

class ProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Mview\Config\Data\Proxy
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Mview\Config\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager', array(), array(), '', false
        );
        $this->dataMock = $this->getMock(
            'Magento\Framework\Mview\Config\Data', array(), array(), '', false
        );
    }

    public function testMergeShared()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Mview\Config\Data')
            ->will($this->returnValue($this->dataMock));
        $this->dataMock->expects($this->once())
            ->method('merge')
            ->with(['some_config']);

        $this->model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Mview\Config\Data',
            true
        );

        $this->model->merge(['some_config']);
    }

    public function testMergeNonShared()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\Mview\Config\Data')
            ->will($this->returnValue($this->dataMock));
        $this->dataMock->expects($this->once())
            ->method('merge')
            ->with(['some_config']);

        $this->model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Mview\Config\Data',
            false
        );

        $this->model->merge(['some_config']);
    }

    public function testGetShared()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Mview\Config\Data')
            ->will($this->returnValue($this->dataMock));
        $this->dataMock->expects($this->once())
            ->method('get')
            ->with('some_path', 'default')
            ->will($this->returnValue('some_value'));

        $this->model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Mview\Config\Data',
            true
        );

        $this->assertEquals('some_value', $this->model->get('some_path', 'default'));
    }

    public function testGetNonShared()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\Mview\Config\Data')
            ->will($this->returnValue($this->dataMock));
        $this->dataMock->expects($this->once())
            ->method('get')
            ->with('some_path', 'default')
            ->will($this->returnValue('some_value'));

        $this->model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Mview\Config\Data',
            false
        );

        $this->assertEquals('some_value', $this->model->get('some_path', 'default'));
    }
}
