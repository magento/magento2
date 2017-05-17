<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\Config;
use Magento\Framework\App\ObjectManager;

class DataTest extends \PHPUnit_Framework_TestCase
{
    const SAMPLE_CONFIG_PATH = 'web/unsecure/base_url';

    const SAMPLE_VALUE = 'http://example.com/';

    /**
     * @var \Magento\Framework\App\Config\Value
     */
    protected $_model;

    public static function setUpBeforeClass()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\Config\Storage\WriterInterface::class
        )->save(
            self::SAMPLE_CONFIG_PATH,
            self::SAMPLE_VALUE
        );
        self::_refreshConfiguration();
    }

    public static function tearDownAfterClass()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\Config\Storage\WriterInterface::class
        )->delete(
            self::SAMPLE_CONFIG_PATH
        );
        self::_refreshConfiguration();
    }

    /**
     * Remove cached configuration and reinitialize the application
     */
    protected static function _refreshConfiguration()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\CacheInterface::class)
            ->clean([\Magento\Framework\App\Config::CACHE_TAG]);
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();
        $appConfig = ObjectManager::getInstance()->get(Config::class);
        $appConfig->clean();
    }

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\Config\Value::class
        );
    }

    public function testIsValueChanged()
    {
        // load the model
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Config\Model\ResourceModel\Config\Data\Collection::class
        );
        $collection->addFieldToFilter(
            'path',
            self::SAMPLE_CONFIG_PATH
        )->addFieldToFilter(
            'scope_id',
            0
        )->addFieldToFilter(
            'scope',
            'default'
        );
        foreach ($collection as $configData) {
            $this->_model = $configData;
            break;
        }
        $this->assertNotEmpty($this->_model->getId());

        // assert
        $this->assertFalse($this->_model->isValueChanged());
        $this->_model->setValue(uniqid());
        $this->assertTrue($this->_model->isValueChanged());
    }

    public function testGetOldValue()
    {
        $this->_model->setPath(self::SAMPLE_CONFIG_PATH);
        $this->assertEquals(self::SAMPLE_VALUE, $this->_model->getOldValue());

        $this->_model->setWebsiteCode('base');
        $this->assertEquals(self::SAMPLE_VALUE, $this->_model->getOldValue());

        $this->_model->setStoreCode('default');
        $this->assertEquals(self::SAMPLE_VALUE, $this->_model->getOldValue());
    }

    public function testGetFieldsetDataValue()
    {
        $this->assertNull($this->_model->getFieldsetDataValue('key'));
        $this->_model->setFieldsetData(['key' => 'value']);
        $this->assertEquals('value', $this->_model->getFieldsetDataValue('key'));
    }

    public function testCRUD()
    {
        $this->_model->setData(
            ['scope' => 'default', 'scope_id' => 0, 'path' => 'test/config/path', 'value' => 'test value']
        );
        $crud = new \Magento\TestFramework\Entity($this->_model, ['value' => 'new value']);
        $crud->testCrud();
    }

    public function testCollection()
    {
        $collection = $this->_model->getCollection();
        $collection->addScopeFilter(
            'test',
            0,
            'test'
        )->addPathFilter(
            'not_existing_path'
        )->addValueFilter(
            'not_existing_value'
        );
        $this->assertEmpty($collection->getItems());
    }
}
