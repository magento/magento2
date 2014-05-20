<?php
/**
 * RouterList model test class
 *
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

class RouterListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\RouterList
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var array
     */
    protected $routerList;

    protected function setUp()
    {
        $this->routerList = array(
            'adminRouter' => array('class' => 'AdminClass', 'disable' => true, 'sortOrder' => 10),
            'frontendRouter' => array('class' => 'FrontClass', 'disable' => false, 'sortOrder' => 10),
            'default' => array('class' => 'DefaultClass', 'disable' => false, 'sortOrder' => 5),
            'someRouter' => array('class' => 'SomeClass', 'disable' => false, 'sortOrder' => 10),
            'anotherRouter' => array('class' => 'AnotherClass', 'disable' => false, 'sortOrder' => 15),
        );

        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $this->model = new \Magento\Framework\App\RouterList($this->objectManagerMock, $this->routerList);
    }

    public function testCurrent()
    {
        $expectedClass = new DefaultClass();
        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('DefaultClass')
            ->will($this->returnValue($expectedClass));

        $this->assertEquals($expectedClass, $this->model->current());
    }

    public function testNext()
    {
        $expectedClass = new FrontClass();
        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('FrontClass')
            ->will($this->returnValue($expectedClass));

        $this->model->next();
        $this->assertEquals($expectedClass, $this->model->current());
    }

    public function testValid()
    {
        $this->assertTrue($this->model->valid());
        $this->model->next();
        $this->assertTrue($this->model->valid());
        $this->model->next();
        $this->assertTrue($this->model->valid());
        $this->model->next();
        $this->assertTrue($this->model->valid());
        $this->model->next();
        $this->assertFalse($this->model->valid());
    }

    public function testRewind()
    {
        $frontClass = new FrontClass();
        $defaultClass = new DefaultClass();

        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('DefaultClass')
            ->will($this->returnValue($defaultClass));

        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with('FrontClass')
            ->will($this->returnValue($frontClass));

        $this->assertEquals($defaultClass, $this->model->current());
        $this->model->next();
        $this->assertEquals($frontClass, $this->model->current());
        $this->model->rewind();
        $this->assertEquals($defaultClass, $this->model->current());
    }

}
