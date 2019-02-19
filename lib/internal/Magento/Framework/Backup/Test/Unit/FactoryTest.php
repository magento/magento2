<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Test\Unit;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Backup\Factory
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new \Magento\Framework\Backup\Factory($this->_objectManager);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCreateWrongType()
    {
        $this->_model->create('WRONG_TYPE');
    }

    /**
     * @param string $type
     * @dataProvider allowedTypesDataProvider
     */
    public function testCreate($type)
    {
        $this->_objectManager->expects($this->once())->method('create')->will($this->returnValue('ModelInstance'));

        $this->assertEquals('ModelInstance', $this->_model->create($type));
    }

    /**
     * @return array
     */
    public function allowedTypesDataProvider()
    {
        return [['db'], ['snapshot'], ['filesystem'], ['media'], ['nomedia']];
    }
}
