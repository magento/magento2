<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure\Element;

use Magento\Config\Model\Config\Structure\AbstractElement;
use Magento\Config\Model\Config\Structure\Element\AbstractComposite;
use Magento\Config\Model\Config\Structure\Element\Iterator;
use Magento\Config\Model\Config\Structure\ElementVisibilityInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractCompositeTest extends TestCase
{
    /**
     * @var AbstractComposite
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var MockObject
     */
    protected $_iteratorMock;

    /**
     * @var Manager|MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var ElementVisibilityInterface|MockObject
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

    protected function setUp(): void
    {
        $this->elementVisibilityMock = $this->getMockBuilder(ElementVisibilityInterface::class)
            ->getMockForAbstractClass();
        $this->_iteratorMock = $this->createMock(Iterator::class);
        $this->_storeManagerMock = $this->createMock(StoreManager::class);
        $this->moduleManagerMock = $this->createMock(Manager::class);
        $this->_model = $this->getMockForAbstractClass(
            AbstractComposite::class,
            [$this->_storeManagerMock, $this->moduleManagerMock, $this->_iteratorMock]
        );
        $objectManagerHelper = new ObjectManagerHelper($this);
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->_model,
            'elementVisibility',
            $this->elementVisibilityMock,
            AbstractElement::class
        );
    }

    protected function tearDown(): void
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
        $this->_iteratorMock->expects($this->once())->method('current')->willReturn(true);
        $this->_iteratorMock->expects($this->once())->method('valid')->willReturn(true);
        $this->assertTrue($this->_model->hasChildren());
    }

    public function testIsVisibleReturnsTrueIfThereAreVisibleChildren()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->willReturn(true);
        $this->_iteratorMock->expects($this->once())->method('current')->willReturn(true);
        $this->_iteratorMock->expects($this->once())->method('valid')->willReturn(true);
        $this->_model->setData(['showInDefault' => 'true'], 'default');
        $this->assertTrue($this->_model->isVisible());
    }

    public function testIsVisibleReturnsTrueIfElementHasFrontEndModel()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->willReturn(true);
        $this->_model->setData(['showInDefault' => 'true', 'frontend_model' => 'Model_Name'], 'default');
        $this->assertTrue($this->_model->isVisible());
    }

    public function testIsVisibleReturnsFalseIfElementHasNoChildrenAndFrontendModel()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->willReturn(true);
        $this->_model->setData(['showInDefault' => 'true'], 'default');
        $this->assertFalse($this->_model->isVisible());
    }
}
