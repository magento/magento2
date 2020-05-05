<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure\Element;

use Magento\Config\Model\Config\Structure\Element\Iterator\Field;
use Magento\Config\Model\Config\Structure\Element\Tab;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TabTest extends TestCase
{
    /**
     * @var Tab
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_iteratorMock;

    protected function setUp(): void
    {
        $this->_iteratorMock = $this->createMock(Field::class);

        $this->_model = (new ObjectManager($this))->getObject(
            Tab::class,
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
