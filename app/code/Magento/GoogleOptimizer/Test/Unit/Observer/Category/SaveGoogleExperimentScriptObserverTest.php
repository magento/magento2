<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleOptimizer\Test\Unit\Observer\Category;

use Magento\Catalog\Model\Category;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleOptimizer\Helper\Data;
use Magento\GoogleOptimizer\Model\Code;
use Magento\GoogleOptimizer\Observer\Category\SaveGoogleExperimentScriptObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveGoogleExperimentScriptObserverTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_helperMock;

    /**
     * @var MockObject
     */
    protected $_eventObserverMock;

    /**
     * @var MockObject
     */
    protected $_categoryMock;

    /**
     * @var MockObject
     */
    protected $_codeMock;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var SaveGoogleExperimentScriptObserver
     */
    protected $_modelObserver;

    /**
     * @var int
     */
    protected $_storeId;

    protected function setUp(): void
    {
        $this->_helperMock = $this->createMock(Data::class);
        $this->_categoryMock = $this->createMock(Category::class);
        $this->_storeId = 0;
        $this->_categoryMock->expects(
            $this->atLeastOnce()
        )->method(
            'getStoreId'
        )->willReturn(
            $this->_storeId
        );
        $event = $this->getMockBuilder(Event::class)
            ->addMethods(['getCategory'])
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getCategory')->willReturn($this->_categoryMock);
        $this->_eventObserverMock = $this->createMock(Observer::class);
        $this->_eventObserverMock->expects($this->once())->method('getEvent')->willReturn($event);
        $this->_codeMock = $this->createMock(Code::class);
        $this->_requestMock = $this->getMockForAbstractClass(RequestInterface::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->_modelObserver = $objectManagerHelper->getObject(
            SaveGoogleExperimentScriptObserver::class,
            ['helper' => $this->_helperMock, 'modelCode' => $this->_codeMock, 'request' => $this->_requestMock]
        );
    }

    public function testCreatingCodeIfRequestIsValid()
    {
        $categoryId = 3;
        $experimentScript = 'some string';

        $this->_categoryMock->expects($this->once())->method('getId')->willReturn($categoryId);
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'isGoogleExperimentActive'
        )->with(
            $this->_storeId
        )->willReturn(
            true
        );

        $this->_requestMock->expects(
            $this->exactly(3)
        )->method(
            'getParam'
        )->with(
            'google_experiment'
        )->willReturn(
            ['code_id' => '', 'experiment_script' => $experimentScript]
        );

        $this->_codeMock->expects(
            $this->once()
        )->method(
            'addData'
        )->with(
            [
                'entity_type' => Code::ENTITY_TYPE_CATEGORY,
                'entity_id' => $categoryId,
                'store_id' => $this->_storeId,
                'experiment_script' => $experimentScript,
            ]
        );
        $this->_codeMock->expects($this->once())->method('save');

        $this->_modelObserver->execute($this->_eventObserverMock);
    }

    /**
     * @param array $params
     * @dataProvider dataProviderWrongRequestForCreating
     */
    public function testCreatingCodeIfRequestIsNotValid($params)
    {
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'isGoogleExperimentActive'
        )->with(
            $this->_storeId
        )->willReturn(
            true
        );

        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'google_experiment'
        )->willReturn(
            $params
        );

        $this->_modelObserver->execute($this->_eventObserverMock);
    }

    /**
     * @return array
     */
    public function dataProviderWrongRequestForCreating()
    {
        return [
            // if param 'google_experiment' is not array
            ['wrong type'],
            // if param 'experiment_script' is missed
            [['code_id' => '']],
            // if param 'code_id' is missed
            [['experiment_script' => '']]];
    }

    public function testEditingCodeIfRequestIsValid()
    {
        $categoryId = 3;
        $experimentScript = 'some string';
        $codeId = 5;

        $this->_categoryMock->expects($this->once())->method('getId')->willReturn($categoryId);
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'isGoogleExperimentActive'
        )->with(
            $this->_storeId
        )->willReturn(
            true
        );

        $this->_requestMock->expects(
            $this->exactly(3)
        )->method(
            'getParam'
        )->with(
            'google_experiment'
        )->willReturn(
            ['code_id' => $codeId, 'experiment_script' => $experimentScript]
        );

        $this->_codeMock->expects($this->once())->method('load')->with($codeId);
        $this->_codeMock->expects($this->once())->method('getId')->willReturn($codeId);

        $this->_codeMock->expects(
            $this->once()
        )->method(
            'addData'
        )->with(
            [
                'entity_type' => Code::ENTITY_TYPE_CATEGORY,
                'entity_id' => $categoryId,
                'store_id' => $this->_storeId,
                'experiment_script' => $experimentScript,
            ]
        );
        $this->_codeMock->expects($this->once())->method('save');

        $this->_modelObserver->execute($this->_eventObserverMock);
    }

    public function testEditingCodeIfCodeModelIsNotFound()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Code does not exist');
        $experimentScript = 'some string';
        $codeId = 5;

        $this->_helperMock->expects(
            $this->once()
        )->method(
            'isGoogleExperimentActive'
        )->with(
            $this->_storeId
        )->willReturn(
            true
        );

        $this->_requestMock->expects(
            $this->exactly(3)
        )->method(
            'getParam'
        )->with(
            'google_experiment'
        )->willReturn(
            ['code_id' => $codeId, 'experiment_script' => $experimentScript]
        );

        $this->_codeMock->expects($this->once())->method('load')->with($codeId);
        $this->_codeMock->expects($this->atLeastOnce())->method('getId')->willReturn(false);
        $this->_codeMock->expects($this->never())->method('save');

        $this->_modelObserver->execute($this->_eventObserverMock);
    }

    public function testRemovingCode()
    {
        $codeId = 5;

        $this->_helperMock->expects(
            $this->once()
        )->method(
            'isGoogleExperimentActive'
        )->with(
            $this->_storeId
        )->willReturn(
            true
        );

        $this->_requestMock->expects(
            $this->exactly(3)
        )->method(
            'getParam'
        )->with(
            'google_experiment'
        )->willReturn(
            ['code_id' => $codeId, 'experiment_script' => '']
        );

        $this->_codeMock->expects($this->once())->method('load')->with($codeId);
        $this->_codeMock->expects($this->once())->method('getId')->willReturn($codeId);

        $this->_codeMock->expects($this->never())->method('save');
        $this->_codeMock->expects($this->once())->method('delete');

        $this->_modelObserver->execute($this->_eventObserverMock);
    }

    public function testManagingCodeIfGoogleExperimentIsDisabled()
    {
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'isGoogleExperimentActive'
        )->with(
            $this->_storeId
        )->willReturn(
            false
        );
        $this->_codeMock->expects($this->never())->method('load');
        $this->_codeMock->expects($this->never())->method('save');
        $this->_codeMock->expects($this->never())->method('delete');

        $this->_modelObserver->execute($this->_eventObserverMock);
    }
}
