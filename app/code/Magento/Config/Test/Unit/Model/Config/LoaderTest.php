<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config;

/**
 * @package Magento\Config\Test\Unit\Model\Config
 */
class LoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Loader
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_configValueFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_configCollection;

    protected function setUp(): void
    {
        $this->_configValueFactory = $this->createPartialMock(
            \Magento\Framework\App\Config\ValueFactory::class,
            ['create', 'getCollection']
        );
        $this->_model = new \Magento\Config\Model\Config\Loader($this->_configValueFactory);
        $this->_configCollection = $this->createMock(\Magento\Config\Model\ResourceModel\Config\Data\Collection::class);
        $this->_configCollection->expects(
            $this->once()
        )->method(
            'addScopeFilter'
        )->with(
            'scope',
            'scopeId',
            'section'
        )->willReturnSelf(
            
        );

        $configDataMock = $this->createMock(\Magento\Framework\App\Config\Value::class);
        $this->_configValueFactory->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $configDataMock
        );
        $configDataMock->expects(
            $this->any()
        )->method(
            'getCollection'
        )->willReturn(
            $this->_configCollection
        );

        $this->_configCollection->expects(
            $this->once()
        )->method(
            'getItems'
        )->willReturn(
            
                [new \Magento\Framework\DataObject(['path' => 'section', 'value' => 10, 'config_id' => 20])]
            
        );
    }

    protected function tearDown(): void
    {
        unset($this->_configValueFactory);
        unset($this->_model);
        unset($this->_configCollection);
    }

    public function testGetConfigByPathInFullMode()
    {
        $expected = ['section' => ['path' => 'section', 'value' => 10, 'config_id' => 20]];
        $this->assertEquals($expected, $this->_model->getConfigByPath('section', 'scope', 'scopeId', true));
    }

    public function testGetConfigByPath()
    {
        $expected = ['section' => 10];
        $this->assertEquals($expected, $this->_model->getConfigByPath('section', 'scope', 'scopeId', false));
    }
}
