<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Config;

class DataTest extends \PHPUnit_Framework_TestCase
{
    const SAMPLE_CONFIG_PATH = 'web/unsecure/base_url';
    const SAMPLE_VALUE = 'http://example.com/';

    /**
     * @var \Magento\Core\Model\Config\Value
     */
    protected $_model;

    public static function setUpBeforeClass()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Config\Storage\Writer\Db')
            ->save(self::SAMPLE_CONFIG_PATH, self::SAMPLE_VALUE);
        self::_refreshConfiguration();
    }

    public static function tearDownAfterClass()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Core\Model\Config\Storage\Writer\Db')
            ->delete(self::SAMPLE_CONFIG_PATH);
        self::_refreshConfiguration();
    }

    /**
     * Remove cached configuration and reinitialize the application
     */
    protected static function _refreshConfiguration()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')
            ->cleanCache(array(\Magento\Core\Model\Config::CACHE_TAG));
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();
    }

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Config\Value');
    }

    public function testIsValueChanged()
    {
        // load the model
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Resource\Config\Data\Collection');
        $collection->addFieldToFilter('path', self::SAMPLE_CONFIG_PATH)->addFieldToFilter('scope_id', 0)
            ->addFieldToFilter('scope', 'default')
        ;
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
        $this->_model->setFieldsetData(array('key' => 'value'));
        $this->assertEquals('value', $this->_model->getFieldsetDataValue('key'));
    }

    public function testCRUD()
    {
        $this->_model->setData(
            array(
                'scope'     => 'default',
                'scope_id'  => 0,
                'path'      => 'test/config/path',
                'value'     => 'test value'
            )
        );
        $crud = new \Magento\TestFramework\Entity($this->_model, array('value' => 'new value'));
        $crud->testCrud();
    }

    public function testCollection()
    {
        $collection = $this->_model->getCollection();
        $collection->addScopeFilter('test', 0, 'test')
            ->addPathFilter('not_existing_path')
            ->addValueFilter('not_existing_value');
        $this->assertEmpty($collection->getItems());
    }
}
