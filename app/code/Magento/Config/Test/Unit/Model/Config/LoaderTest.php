<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config;

use Magento\Config\Model\Config\Loader;
use Magento\Config\Model\ResourceModel\Config\Data\Collection;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    /**
     * @var Loader
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_configValueFactory;

    /**
     * @var MockObject
     */
    protected $_configCollection;

    protected function setUp(): void
    {
        $this->_configValueFactory = $this->createPartialMock(
            ValueFactory::class,
            ['create', 'getCollection']
        );
        $this->_model = new Loader($this->_configValueFactory);
        $this->_configCollection = $this->createMock(Collection::class);
        $this->_configCollection->expects(
            $this->once()
        )->method(
            'addScopeFilter'
        )->with(
            'scope',
            'scopeId',
            'section'
        )->will(
            $this->returnSelf()
        );

        $configDataMock = $this->createMock(Value::class);
        $this->_configValueFactory->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($configDataMock)
        );
        $configDataMock->expects(
            $this->any()
        )->method(
            'getCollection'
        )->will(
            $this->returnValue($this->_configCollection)
        );

        $this->_configCollection->expects(
            $this->once()
        )->method(
            'getItems'
        )->will(
            $this->returnValue(
                [new DataObject(['path' => 'section', 'value' => 10, 'config_id' => 20])]
            )
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
