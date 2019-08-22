<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\Element;

use Magento\Config\Model\Config\Structure\ElementVisibilityInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Abstract composite test.
 */
class AbstractCompositeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Element\AbstractComposite
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_iteratorMock;

    /**
     * @var \Magento\Framework\Module\ModuleManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var ElementVisibilityInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $elementVisibilityMock;

    /**
     * Test element data
     *
     * @var array
     */
    protected $_testData = [
        'id' => 'elementId',
        'label' => 'Element Label',
        'someAttribute' => 'Some attribute value',
        'children' => ['someGroup' => []],
    ];

    protected function setUp()
    {
        $this->elementVisibilityMock = $this->getMockBuilder(ElementVisibilityInterface::class)
            ->getMockForAbstractClass();
        $this->_iteratorMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Iterator::class);
        $this->_storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->moduleManagerMock = $this->createMock(\Magento\Framework\Module\ModuleManagerInterface::class);
        $this->_model = $this->getMockForAbstractClass(
            \Magento\Config\Model\Config\Structure\Element\AbstractComposite::class,
            [$this->_storeManagerMock, $this->moduleManagerMock, $this->_iteratorMock]
        );
        $objectManagerHelper = new ObjectManagerHelper($this);
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->_model,
            'elementVisibility',
            $this->elementVisibilityMock,
            \Magento\Config\Model\Config\Structure\AbstractElement::class
        );
    }

    protected function tearDown()
    {
        unset($this->_iteratorMock);
        unset($this->_storeManagerMock);
        unset($this->_model);
    }

    public function testSetDataInitializesChildIterator()
    {
        $this->_iteratorMock->expects(
            $this->once()
        )->method(
            'setElements'
        )->with(
            ['someGroup' => []],
            'scope'
        );
        $this->_model->setData($this->_testData, 'scope');
    }

    public function testSetDataInitializesChildIteratorWithEmptyArrayIfNoChildrenArePresent()
    {
        $this->_iteratorMock->expects($this->once())->method('setElements')->with([], 'scope');
        $this->_model->setData([], 'scope');
    }

    public function testHasChildrenReturnsFalseIfThereAreNoChildren()
    {
        $this->assertFalse($this->_model->hasChildren());
    }

    public function testHasChildrenReturnsTrueIfThereAreVisibleChildren()
    {
        $this->_iteratorMock->expects($this->once())->method('current')->will($this->returnValue(true));
        $this->_iteratorMock->expects($this->once())->method('valid')->will($this->returnValue(true));
        $this->assertTrue($this->_model->hasChildren());
    }

    public function testIsVisibleReturnsTrueIfThereAreVisibleChildren()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->_iteratorMock->expects($this->once())->method('current')->will($this->returnValue(true));
        $this->_iteratorMock->expects($this->once())->method('valid')->will($this->returnValue(true));
        $this->_model->setData(['showInDefault' => 'true'], 'default');
        $this->assertTrue($this->_model->isVisible());
    }

    public function testIsVisibleReturnsTrueIfElementHasFrontEndModel()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->_model->setData(['showInDefault' => 'true', 'frontend_model' => 'Model_Name'], 'default');
        $this->assertTrue($this->_model->isVisible());
    }

    public function testIsVisibleReturnsFalseIfElementHasNoChildrenAndFrontendModel()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->_model->setData(['showInDefault' => 'true'], 'default');
        $this->assertFalse($this->_model->isVisible());
    }
}
