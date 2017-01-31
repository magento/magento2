<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Test\Unit\Observer\Category;

class SaveGoogleExperimentScriptObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventObserverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_categoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_codeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \Magento\GoogleOptimizer\Observer\Category\SaveGoogleExperimentScriptObserver
     */
    protected $_modelObserver;

    /**
     * @var int
     */
    protected $_storeId;

    protected function setUp()
    {
        $this->_helperMock = $this->getMock('Magento\GoogleOptimizer\Helper\Data', [], [], '', false);
        $this->_categoryMock = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $this->_storeId = 0;
        $this->_categoryMock->expects(
            $this->atLeastOnce()
        )->method(
            'getStoreId'
        )->will(
            $this->returnValue($this->_storeId)
        );
        $event = $this->getMock('Magento\Framework\Event', ['getCategory'], [], '', false);
        $event->expects($this->once())->method('getCategory')->will($this->returnValue($this->_categoryMock));
        $this->_eventObserverMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $this->_eventObserverMock->expects($this->once())->method('getEvent')->will($this->returnValue($event));
        $this->_codeMock = $this->getMock('Magento\GoogleOptimizer\Model\Code', [], [], '', false);
        $this->_requestMock = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_modelObserver = $objectManagerHelper->getObject(
            'Magento\GoogleOptimizer\Observer\Category\SaveGoogleExperimentScriptObserver',
            ['helper' => $this->_helperMock, 'modelCode' => $this->_codeMock, 'request' => $this->_requestMock]
        );
    }

    public function testCreatingCodeIfRequestIsValid()
    {
        $categoryId = 3;
        $experimentScript = 'some string';

        $this->_categoryMock->expects($this->once())->method('getId')->will($this->returnValue($categoryId));
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'isGoogleExperimentActive'
        )->with(
            $this->_storeId
        )->will(
            $this->returnValue(true)
        );

        $this->_requestMock->expects(
            $this->exactly(3)
        )->method(
            'getParam'
        )->with(
            'google_experiment'
        )->will(
            $this->returnValue(['code_id' => '', 'experiment_script' => $experimentScript])
        );

        $this->_codeMock->expects(
            $this->once()
        )->method(
            'addData'
        )->with(
            [
                'entity_type' => \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_CATEGORY,
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
        )->will(
            $this->returnValue(true)
        );

        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'google_experiment'
        )->will(
            $this->returnValue($params)
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

        $this->_categoryMock->expects($this->once())->method('getId')->will($this->returnValue($categoryId));
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'isGoogleExperimentActive'
        )->with(
            $this->_storeId
        )->will(
            $this->returnValue(true)
        );

        $this->_requestMock->expects(
            $this->exactly(3)
        )->method(
            'getParam'
        )->with(
            'google_experiment'
        )->will(
            $this->returnValue(['code_id' => $codeId, 'experiment_script' => $experimentScript])
        );

        $this->_codeMock->expects($this->once())->method('load')->with($codeId);
        $this->_codeMock->expects($this->once())->method('getId')->will($this->returnValue($codeId));

        $this->_codeMock->expects(
            $this->once()
        )->method(
            'addData'
        )->with(
            [
                'entity_type' => \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_CATEGORY,
                'entity_id' => $categoryId,
                'store_id' => $this->_storeId,
                'experiment_script' => $experimentScript,
            ]
        );
        $this->_codeMock->expects($this->once())->method('save');

        $this->_modelObserver->execute($this->_eventObserverMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Code does not exist
     */
    public function testEditingCodeIfCodeModelIsNotFound()
    {
        $experimentScript = 'some string';
        $codeId = 5;

        $this->_helperMock->expects(
            $this->once()
        )->method(
            'isGoogleExperimentActive'
        )->with(
            $this->_storeId
        )->will(
            $this->returnValue(true)
        );

        $this->_requestMock->expects(
            $this->exactly(3)
        )->method(
            'getParam'
        )->with(
            'google_experiment'
        )->will(
            $this->returnValue(['code_id' => $codeId, 'experiment_script' => $experimentScript])
        );

        $this->_codeMock->expects($this->once())->method('load')->with($codeId);
        $this->_codeMock->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(false));
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
        )->will(
            $this->returnValue(true)
        );

        $this->_requestMock->expects(
            $this->exactly(3)
        )->method(
            'getParam'
        )->with(
            'google_experiment'
        )->will(
            $this->returnValue(['code_id' => $codeId, 'experiment_script' => ''])
        );

        $this->_codeMock->expects($this->once())->method('load')->with($codeId);
        $this->_codeMock->expects($this->once())->method('getId')->will($this->returnValue($codeId));

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
        )->will(
            $this->returnValue(false)
        );
        $this->_codeMock->expects($this->never())->method('load');
        $this->_codeMock->expects($this->never())->method('save');
        $this->_codeMock->expects($this->never())->method('delete');

        $this->_modelObserver->execute($this->_eventObserverMock);
    }
}
