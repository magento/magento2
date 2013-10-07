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
 * obtain it through the world-wide-web, please send an e-mail
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GoogleOptimizer\Model\Observer\Category;

class SaveTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\GoogleOptimizer\Model\Observer\Category\Save
     */
    protected $_modelObserver;

    /**
     * @var int
     */
    protected $_storeId;

    protected function setUp()
    {
        $this->_helperMock = $this->getMock('Magento\GoogleOptimizer\Helper\Data', array(), array(), '', false);
        $this->_categoryMock = $this->getMock('Magento\Catalog\Model\Category', array(), array(), '', false);
        $this->_storeId = 0;
        $this->_categoryMock->expects($this->atLeastOnce())->method('getStoreId')
            ->will($this->returnValue($this->_storeId));
        $event = $this->getMock('Magento\Event', array('getCategory'), array(), '', false);
        $event->expects($this->once())->method('getCategory')->will($this->returnValue($this->_categoryMock));
        $this->_eventObserverMock = $this->getMock('Magento\Event\Observer', array(), array(), '', false);
        $this->_eventObserverMock->expects($this->once())->method('getEvent')->will($this->returnValue($event));
        $this->_codeMock = $this->getMock('Magento\GoogleOptimizer\Model\Code', array(), array(), '', false);
        $this->_requestMock = $this->getMock('Magento\Core\Controller\Request\Http', array(), array(), '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_modelObserver = $objectManagerHelper->getObject(
            'Magento\GoogleOptimizer\Model\Observer\Category\Save',
            array(
                'helper' => $this->_helperMock,
                'modelCode' => $this->_codeMock,
                'request' => $this->_requestMock,
            )
        );
    }

    public function testCreatingCodeIfRequestIsValid()
    {
        $categoryId = 3;
        $experimentScript = 'some string';

        $this->_categoryMock->expects($this->once())->method('getId')->will($this->returnValue($categoryId));
        $this->_helperMock->expects($this->once())->method('isGoogleExperimentActive')->with($this->_storeId)
            ->will($this->returnValue(true));

        $this->_requestMock->expects($this->once())->method('getParam')->with('google_experiment')
            ->will($this->returnValue(array(
                'code_id' => '',
                'experiment_script' => $experimentScript,
            )));

        $this->_codeMock->expects($this->once())->method('addData')->with(array(
            'entity_type' => \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_CATEGORY,
            'entity_id' => $categoryId,
            'store_id' => $this->_storeId,
            'experiment_script' => $experimentScript,
        ));
        $this->_codeMock->expects($this->once())->method('save');

        $this->_modelObserver->saveGoogleExperimentScript($this->_eventObserverMock);
    }

    /**
     * @param array $params
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Wrong request parameters
     * @dataProvider dataProviderWrongRequestForCreating
     */
    public function testCreatingCodeIfRequestIsNotValid($params)
    {
        $this->_helperMock->expects($this->once())->method('isGoogleExperimentActive')->with($this->_storeId)
            ->will($this->returnValue(true));

        $this->_requestMock->expects($this->once())->method('getParam')->with('google_experiment')
            ->will($this->returnValue($params));

        $this->_modelObserver->saveGoogleExperimentScript($this->_eventObserverMock);
    }

    /**
     * @return array
     */
    public function dataProviderWrongRequestForCreating()
    {
        return array(
            // if param 'google_experiment' is not array
            array('wrong type'),
            // if param 'experiment_script' is missed
            array(
                array('code_id' => ''),
            ),
            // if param 'code_id' is missed
            array(
                array('experiment_script' => ''),
            ),
        );
    }

    public function testEditingCodeIfRequestIsValid()
    {
        $categoryId = 3;
        $experimentScript = 'some string';
        $codeId = 5;

        $this->_categoryMock->expects($this->once())->method('getId')->will($this->returnValue($categoryId));
        $this->_helperMock->expects($this->once())->method('isGoogleExperimentActive')->with($this->_storeId)
            ->will($this->returnValue(true));

        $this->_requestMock->expects($this->once())->method('getParam')->with('google_experiment')
            ->will($this->returnValue(array(
                'code_id' => $codeId,
                'experiment_script' => $experimentScript,
            )));

        $this->_codeMock->expects($this->once())->method('load')->with($codeId);
        $this->_codeMock->expects($this->once())->method('getId')->will($this->returnValue($codeId));

        $this->_codeMock->expects($this->once())->method('addData')->with(array(
            'entity_type' => \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_CATEGORY,
            'entity_id' => $categoryId,
            'store_id' => $this->_storeId,
            'experiment_script' => $experimentScript,
        ));
        $this->_codeMock->expects($this->once())->method('save');

        $this->_modelObserver->saveGoogleExperimentScript($this->_eventObserverMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Code does not exist
     */
    public function testEditingCodeIfCodeModelIsNotFound()
    {
        $experimentScript = 'some string';
        $codeId = 5;

        $this->_helperMock->expects($this->once())->method('isGoogleExperimentActive')->with($this->_storeId)
            ->will($this->returnValue(true));

        $this->_requestMock->expects($this->once())->method('getParam')->with('google_experiment')
            ->will($this->returnValue(array(
                'code_id' => $codeId,
                'experiment_script' => $experimentScript,
            )));

        $this->_codeMock->expects($this->once())->method('load')->with($codeId);
        $this->_codeMock->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(false));
        $this->_codeMock->expects($this->never())->method('save');

        $this->_modelObserver->saveGoogleExperimentScript($this->_eventObserverMock);
    }

    public function testRemovingCode()
    {
        $codeId = 5;

        $this->_helperMock->expects($this->once())->method('isGoogleExperimentActive')->with($this->_storeId)
            ->will($this->returnValue(true));

        $this->_requestMock->expects($this->once())->method('getParam')->with('google_experiment')
            ->will($this->returnValue(array(
                'code_id' => $codeId,
                'experiment_script' => '',
            )));

        $this->_codeMock->expects($this->once())->method('load')->with($codeId);
        $this->_codeMock->expects($this->once())->method('getId')->will($this->returnValue($codeId));

        $this->_codeMock->expects($this->never())->method('save');
        $this->_codeMock->expects($this->once())->method('delete');

        $this->_modelObserver->saveGoogleExperimentScript($this->_eventObserverMock);
    }

    public function testManagingCodeIfGoogleExperimentIsDisabled()
    {
        $this->_helperMock->expects($this->once())->method('isGoogleExperimentActive')->with($this->_storeId)
            ->will($this->returnValue(false));
        $this->_codeMock->expects($this->never())->method('load');
        $this->_codeMock->expects($this->never())->method('save');
        $this->_codeMock->expects($this->never())->method('delete');

        $this->_modelObserver->saveGoogleExperimentScript($this->_eventObserverMock);
    }
}
