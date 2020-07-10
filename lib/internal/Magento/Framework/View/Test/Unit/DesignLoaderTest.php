<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\View\DesignLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DesignLoaderTest extends TestCase
{
    /**
     * @var DesignLoader
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_areaListMock;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var State|MockObject
     */
    protected $appState;

    protected function setUp(): void
    {
        $this->_areaListMock = $this->createMock(AreaList::class);
        $this->_requestMock = $this->createMock(Http::class);
        $this->appState = $this->createMock(State::class);
        $this->_model = new DesignLoader(
            $this->_requestMock,
            $this->_areaListMock,
            $this->appState
        );
    }

    public function testLoad()
    {
        $area = $this->createMock(Area::class);
        $this->appState->expects($this->once())->method('getAreaCode')->willReturn('area');
        $this->_areaListMock->expects($this->once())->method('getArea')->with('area')->willReturn($area);
        $area->expects($this->at(0))->method('load')
            ->with(Area::PART_DESIGN)->willReturn($area);
        $area->expects($this->at(1))->method('load')
            ->with(Area::PART_TRANSLATE)->willReturn($area);
        $this->_model->load($this->_requestMock);
    }
}
