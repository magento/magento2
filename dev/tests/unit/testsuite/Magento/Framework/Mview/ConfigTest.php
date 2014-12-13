<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Mview;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Mview\Config
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Mview\Config\Data
     */
    protected $dataMock;

    protected function setUp()
    {
        $this->dataMock = $this->getMock(
            'Magento\Framework\Mview\Config\Data', [], [], '', false
        );
        $this->model = new Config(
            $this->dataMock
        );
    }

    public function testGetViews()
    {
        $this->dataMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['some_data']));
        $this->assertEquals(['some_data'], $this->model->getViews());
    }

    public function testGetView()
    {
        $this->dataMock->expects($this->once())
            ->method('get')
            ->with('some_view')
            ->will($this->returnValue(['some_data']));
        $this->assertEquals(['some_data'], $this->model->getView('some_view'));
    }
}
