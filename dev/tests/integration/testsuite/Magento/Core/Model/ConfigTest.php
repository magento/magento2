<?php
/**
 * Integration test for \Magento\Core\Model\Config
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model;

/**
 * First part of \Magento\Core\Model\Config testing:
 * - general behaviour is tested
 *
 * @see \Magento\Core\Model\ConfigFactoryTest
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        /** @var \Magento\Core\Model\Cache\StateInterface $cacheState */
        $cacheState = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Core\Model\Cache\StateInterface');
        $cacheState->setEnabled('config', false);
    }

    public function testSetNode()
    {
        $model = $this->_createModel();
        /* some existing node should be used */
        $model->setNode('default/node/with/value', 'test');
        $this->assertEquals('test', (string) $model->getNode('default/node/with/value'));
    }

    public function testDetermineOmittedNamespace()
    {
        $model = $this->_createModel();
        $this->assertEquals('cms', $model->determineOmittedNamespace('cms'));
        $this->assertEquals('Magento_Cms', $model->determineOmittedNamespace('cms', true));
        $this->assertEquals('', $model->determineOmittedNamespace('nonexistent'));
        $this->assertEquals('', $model->determineOmittedNamespace('nonexistent', true));
    }

    public function testGetModuleDir()
    {
        $model = $this->_createModel();
        foreach (array('etc', 'sql', 'data', 'i18n') as $type) {
            $dir = $model->getModuleDir($type, 'Magento_Core');
            $this->assertStringEndsWith($type, $dir);
            $this->assertContains('Magento' . DIRECTORY_SEPARATOR . 'Core', $dir);
        }
        $this->assertTrue(is_dir($this->_createModel()->getModuleDir('etc', 'Magento_Core')));
    }

    public function testGetStoresConfigByPath()
    {
        $model = $this->_createModel();

        // default
        $baseUrl = $model->getStoresConfigByPath('web/unsecure/base_url');
        $this->assertArrayHasKey(0, $baseUrl);
        $this->assertArrayHasKey(1, $baseUrl);

        // $allowValues
        $baseUrl = $model->getStoresConfigByPath('web/unsecure/base_url', array(uniqid()));
        $this->assertEquals(array(), $baseUrl);

        // store code
        $baseUrl = $model->getStoresConfigByPath('web/unsecure/base_url', array(), 'code');
        $this->assertArrayHasKey('default', $baseUrl);
        $this->assertArrayHasKey('admin', $baseUrl);

        // store name
        $baseUrl = $model->getStoresConfigByPath('web/unsecure/base_url', array(), 'name');
        $this->assertArrayHasKey('Default Store View', $baseUrl);
        $this->assertArrayHasKey('Admin', $baseUrl);
    }

    /**
     * Instantiate \Magento\Core\Model\Config and initialize (load configuration) if needed
     *
     * @param array $arguments
     * @return \Magento\Core\Model\Config
     */
    protected function _createModel(array $arguments = array())
    {
        /** @var $model \Magento\Core\Model\Config */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Config', $arguments);
        return $model;
    }
}
