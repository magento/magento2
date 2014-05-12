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

class TtlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\System\Config\Backend\Ttl
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    protected function setUp()
    {
        $this->_config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\PageCache\Model\System\Config\Backend\Ttl');
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
        return array(
            array(125, 'ttl_1'),
            array(0, 'ttl_2'),
        );
    }

    /**
     * @dataProvider beforeSaveDataProviderWithException
     *
     * @param $value
     * @param $path
     */
    public function testBeforeSaveWithException($value, $path)
    {
        $this->setExpectedException('\Magento\Framework\Model\Exception');
        $this->_prepareData($value, $path);
    }

    public function beforeSaveDataProviderWithException()
    {
        return array(
            array('', 'ttl_3'),
            array('sdfg', 'ttl_4')
        );
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
