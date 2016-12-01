<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit;

class DesignLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\DesignLoader
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_areaListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appState;

    protected function setUp()
    {
        $this->_areaListMock = $this->getMock(\Magento\Framework\App\AreaList::class, [], [], '', false);
        $this->_requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->appState = $this->getMock(\Magento\Framework\App\State::class, [], [], '', false);
        $this->_model = new \Magento\Framework\View\DesignLoader(
            $this->_requestMock,
            $this->_areaListMock,
            $this->appState
        );
    }

    public function testLoad()
    {
        $area = $this->getMock(\Magento\Framework\App\Area::class, [], [], '', false);
        $this->appState->expects($this->once())->method('getAreaCode')->will($this->returnValue('area'));
        $this->_areaListMock->expects($this->once())->method('getArea')->with('area')->will($this->returnValue($area));
        $area->expects($this->at(0))->method('load')
            ->with(\Magento\Framework\App\Area::PART_DESIGN)->will($this->returnValue($area));
        $area->expects($this->at(1))->method('load')
            ->with(\Magento\Framework\App\Area::PART_TRANSLATE)->will($this->returnValue($area));
        $this->_model->load($this->_requestMock);
    }
}
