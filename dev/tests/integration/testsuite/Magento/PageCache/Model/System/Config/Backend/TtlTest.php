<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\System\Config\Backend;

class TtlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\PageCache\Model\System\Config\Backend\Ttl
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    protected function setUp(): void
    {
        $this->_config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\PageCache\Model\System\Config\Backend\Ttl::class);
    }

    /**
     * @dataProvider beforeSaveDataProvider
     *
     * @param $value
     * @param $path
     */
    public function testBeforeSave($value, $path)
    {
        $this->_prepareData($value, $path);
    }

    public function beforeSaveDataProvider()
    {
        return [
            [125, 'ttl_1'],
            [0, 'ttl_2'],
        ];
    }

    /**
     * @dataProvider beforeSaveDataProviderWithException
     *
     * @param $value
     * @param $path
     */
    public function testBeforeSaveWithException($value, $path)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->_prepareData($value, $path);
    }

    public function beforeSaveDataProviderWithException()
    {
        return [
            ['', 'ttl_3'],
            ['sdfg', 'ttl_4']
        ];
    }

    /**
     * @param $value
     * @param $path
     */
    protected function _prepareData($value, $path)
    {
        $this->_model->setValue($value);
        $this->_model->setPath($path);
        $this->_model->setField($path);
        $this->_model->save();
    }
}
