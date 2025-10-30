<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\System\Config\Backend;

class VarnishTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\PageCache\Model\System\Config\Backend\Varnish
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\MutableScopeConfigInterface
     */
    protected $_config;

    protected function setUp(): void
    {
        $this->_config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\Config\MutableScopeConfigInterface::class
        );
        $data = [
            'access_list' => 'localhost',
            'backend_host' => 'localhost',
            'backend_port' => 8080,
            'ttl' => 120,
        ];
        $this->_config->setValue('system/full_page_cache/default', $data);
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\PageCache\Model\System\Config\Backend\Varnish::class
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

    public static function beforeSaveDataProvider()
    {
        return [
            ['localhost', 'access_list', 'localhost', false],
            ['localhost', 'backend_host', 'localhost', false],
            [8081, 'backend_port', 8081, false],
            [125, 'ttl', 125, false],
            ['localhost', 'access_list', 'localhost', true],
            ['', 'backend_host', 'localhost', true],
            [0, 'backend_port', 8080, true],
            [0, 'ttl', 120, true]
        ];
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

    public static function afterLoadDataProvider()
    {
        return [
            ['access_list', 'localhost', true],
            ['backend_host', 'localhost', true],
            ['backend_port', 8080, true],
            ['ttl', 120, true]
        ];
    }
}
