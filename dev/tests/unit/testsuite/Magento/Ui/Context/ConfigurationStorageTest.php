<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Context;

use Magento\Framework\Data\Collection as DataCollection;

/**
 * Class ConfigurationStorageTest
 * @package Magento\Ui
 */
class ConfigurationStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $componentStorage = [];

    /**
     * @var array
     */
    protected $dataStorage = [];

    /**
     * @var array
     */
    protected $metaStorage = [];

    /**
     * @var DataCollection[]
     */
    protected $collectionStorage = [];

    /**
     * @var array
     */
    protected $cloudDataStorage = [];

    /**
     * @var ConfigurationStorage
     */
    protected $configurationStorage;

    public function setUp()
    {
        $this->configurationStorage = new ConfigurationStorage();
    }

    public function testAddGetComponentsData()
    {
        $configuration = ['key' => 'value'];
        $name = 'myName';
        $parentName = 'thisParentName';
        $configurationModel = new Configuration($name, $parentName, $configuration);
        $this->componentStorage = [$configurationModel->getName() => $configurationModel];
        $this->configurationStorage->addComponentsData($configurationModel);

        $this->assertEquals($this->componentStorage, $this->configurationStorage->getComponentsData(null));
        $this->assertEquals(null, $this->configurationStorage->getComponentsData('someKey'));
        $this->assertEquals($configurationModel, $this->configurationStorage->getComponentsData($name));
    }

    public function testRemoveComponentsData()
    {
        $configuration = ['key' => 'value'];
        $name = 'myName';
        $parentName = 'thisParentName';
        $configurationModel = new Configuration($name, $parentName, $configuration);
        $this->componentStorage = [$configurationModel->getName() => $configurationModel];
        $this->configurationStorage->addComponentsData($configurationModel);
        $this->assertEquals($configurationModel, $this->configurationStorage->getComponentsData($name));
        $this->configurationStorage->removeComponentsData($configurationModel);
        $this->assertEquals(null, $this->configurationStorage->getComponentsData($name));
    }

    public function testAddGetData()
    {
        $name = 'myName';
        $dataSource = [
            'data' => ['key' => 'value'],
            'config' => ['key' => 'value'],
        ];
        $this->configurationStorage->addDataSource($name, $dataSource);
        $this->assertEquals([$name => $dataSource], $this->configurationStorage->getDataSource(null));
        $this->assertEquals(null, $this->configurationStorage->getDataSource('someKey'));
        $this->assertEquals($dataSource, $this->configurationStorage->getDataSource($name));
    }

    public function testUpdateRemoveData()
    {
        $dataSource = [
            'data' => ['key' => 'value'],
            'config' => ['key' => 'value'],
        ];
        $key = 'myKey';
        $this->configurationStorage->addDataSource($key, $dataSource);
        $this->assertEquals($dataSource, $this->configurationStorage->getDataSource($key));
        $dataSource = [
            'data' => ['key1' => 'value1'],
            'config' => ['key1' => 'value1'],
        ];
        $this->configurationStorage->updateDataSource($key, $dataSource);
        $this->assertEquals($dataSource, $this->configurationStorage->getDataSource($key));
        $this->configurationStorage->removeDataSource($key);
        $this->assertEquals(null, $this->configurationStorage->getDataSource($key));
    }

    public function testAddGetMeta()
    {
        $data = ['key' => 'value'];
        $key = 'myName';
        $this->configurationStorage->addMeta($key, $data);
        $this->assertEquals([$key => $data], $this->configurationStorage->getMeta(null));
        $this->assertEquals(null, $this->configurationStorage->getMeta('someKey'));
        $this->assertEquals($data, $this->configurationStorage->getMeta($key));
    }

    public function testUpdateRemoveMeta()
    {
        $data = ['key' => 'value'];
        $key = 'myKey';
        $this->configurationStorage->addMeta($key, $data);
        $this->assertEquals($data, $this->configurationStorage->getMeta($key));
        $data = ['key1' => 'value1'];
        $this->configurationStorage->updateMeta($key, $data);
        $this->assertEquals($data, $this->configurationStorage->getMeta($key));
        $this->configurationStorage->removeMeta($key);
        $this->assertEquals(null, $this->configurationStorage->getMeta($key));
    }

    public function testAddGetDataCollection()
    {
        $key = 'myName';
        $dataCollection = $this->getMock('Magento\Framework\Data\CollectionDataSourceInterface', [], [], '', false);
        $this->configurationStorage->addDataCollection($key, $dataCollection);

        $this->assertEquals([$key => $dataCollection], $this->configurationStorage->getDataCollection(null));
        $this->assertEquals(null, $this->configurationStorage->getDataCollection('someKey'));
        $this->assertEquals($dataCollection, $this->configurationStorage->getDataCollection($key));
    }

    public function testRemoveDataCollection()
    {
        $key = 'myName';
        $dataCollection = $this->getMock('Magento\Framework\Data\CollectionDataSourceInterface', [], [], '', false);
        $update = clone $dataCollection;
        $this->configurationStorage->addDataCollection($key, $dataCollection);
        $this->assertEquals($dataCollection, $this->configurationStorage->getDataCollection($key));
        $this->configurationStorage->updateDataCollection($key, $update);
        $this->assertEquals($update, $this->configurationStorage->getDataCollection($key));
    }
}
