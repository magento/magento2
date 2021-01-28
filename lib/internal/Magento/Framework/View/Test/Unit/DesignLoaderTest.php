<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit;

class DesignLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\DesignLoader
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_areaListMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_requestMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $appState;

    protected function setUp(): void
    {
        $this->_areaListMock = $this->createMock(\Magento\Framework\App\AreaList::class);
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->appState = $this->createMock(\Magento\Framework\App\State::class);
        $this->_model = new \Magento\Framework\View\DesignLoader(
            $this->_requestMock,
            $this->_areaListMock,
            $this->appState
        );
    }

    public function testLoad()
    {
        $area = $this->createMock(\Magento\Framework\App\Area::class);
        $this->appState->expects($this->once())->method('getAreaCode')->willReturn('area');
        $this->_areaListMock->expects($this->once())->method('getArea')->with('area')->willReturn($area);
        $area->expects($this->at(0))->method('load')
            ->with(\Magento\Framework\App\Area::PART_DESIGN)->willReturn($area);
        $area->expects($this->at(1))->method('load')
            ->with(\Magento\Framework\App\Area::PART_TRANSLATE)->willReturn($area);
        $this->_model->load($this->_requestMock);
    }
}
