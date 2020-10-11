<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure\Element;

use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\Structure\Element\FlyweightFactory;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Config\Model\Config\Structure\Element\Section;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FlyweightFactoryTest extends TestCase
{
    /**
     * @var FlyweightFactory
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_model = new FlyweightFactory(
            $this->_objectManagerMock
        );
    }

    protected function tearDown(): void
    {
        unset($this->_model);
        unset($this->_objectManagerMock);
    }

    public function testCreate()
    {
        $this->_objectManagerMock->expects(
            $this->any()
        )->method(
            'create'
        )->willReturnMap(
            [
                [Section::class, [], 'sectionObject'],
                [Group::class, [], 'groupObject'],
                [Field::class, [], 'fieldObject'],
            ]
        );
        $this->assertEquals('sectionObject', $this->_model->create('section'));
        $this->assertEquals('groupObject', $this->_model->create('group'));
        $this->assertEquals('fieldObject', $this->_model->create('field'));
    }
}
