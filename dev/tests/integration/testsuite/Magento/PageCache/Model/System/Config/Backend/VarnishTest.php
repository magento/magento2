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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\PageCache\Model\System\Config\Backend;

class VarnishTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\System\Config\Backend\Varnish
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\MutableScopeConfigInterface
     */
    protected $_config;

    protected function setUp()
    {
        $this->_config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Config\MutableScopeConfigInterface'
        );
        $data = array(
            'access_list' => 'localhost',
            'backend_host' => 'localhost',
            'backend_port' => 8080,
            'ttl' => 120
        );
        $this->_config->setValue('system/full_page_cache/default', $data);
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\PageCache\Model\System\Config\Backend\Varnish'
        );
    }

    /**
     * @dataProvider beforeSaveDataProvider
     *
     * @param $value
     * @param $path
     * @param $expected
     * @param $needUpdate
     */
    public function testBeforeSave($value, $path, $expected, $needUpdate)
    {
        if ($needUpdate) {
            $this->_model->load($path, 'path');
        }

        $this->_model->setValue($value);
        $this->_model->setPath($path);
        $this->_model->setField($path);
        $this->_model->save();
        $value = $this->_model->getValue();

        $this->assertEquals($value, $expected);
    }

    public function beforeSaveDataProvider()
    {
        return array(
            array('localhost', 'access_list', 'localhost', false),
            array('localhost', 'backend_host', 'localhost', false),
            array(8081, 'backend_port', 8081, false),
            array(125, 'ttl', 125, false),
            array('localhost', 'access_list', 'localhost', true),
            array('', 'backend_host', 'localhost', true),
            array(0, 'backend_port', 8080, true),
            array(0, 'ttl', 120, true)
        );
    }

    /**
     * @dataProvider afterLoadDataProvider
     *
     * @param $path
     * @param $expected
     * @param $needUpdate
     */
    public function testAfterLoad($path, $expected, $needUpdate)
    {
        if ($needUpdate) {
            $this->_model->load($path, 'path');
        }
        $this->_model->setValue('');
        $this->_model->setPath($path);
        $this->_model->setField($path);
        $this->_model->save();
        $value = $this->_model->getValue();

        $this->assertEquals($value, $expected);
    }

    public function afterLoadDataProvider()
    {
        return array(
            array('access_list', 'localhost', true),
            array('backend_host', 'localhost', true),
            array('backend_port', 8080, true),
            array('ttl', 120, true)
        );
    }
}
