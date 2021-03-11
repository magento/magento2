<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\Element;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class TabTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Tab
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_iteratorMock;

    protected function setUp(): void
    {
        $this->_iteratorMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Iterator\Field::class);

        $this->_model = (new ObjectManager($this))->getObject(
            \Magento\Config\Model\Config\Structure\Element\Tab::class,
            ['childrenIterator' => $this->_iteratorMock]
        );
    }

    protected function tearDown(): void
    {
        unset($this->_model);
        unset($this->_iteratorMock);
    }

    public function testIsVisibleOnlyChecksPresenceOfChildren()
    {
        $this->_model->setData(['showInStore' => 0, 'showInWebsite' => 0, 'showInDefault' => 0], 'store');
        $this->_iteratorMock->expects($this->once())->method('current')->willReturn(true);
        $this->_iteratorMock->expects($this->once())->method('valid')->willReturn(true);
        $this->assertTrue($this->_model->isVisible());
    }
}
