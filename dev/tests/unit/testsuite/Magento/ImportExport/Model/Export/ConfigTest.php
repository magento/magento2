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
namespace Magento\ImportExport\Model\Export;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var string
     */
    protected $_cacheId = 'some_id';

    /**
     * @var \Magento\ImportExport\Model\Export\Config
     */
    protected $_model;

    protected function setUp()
    {
        $this->_readerMock = $this->getMock(
            'Magento\ImportExport\Model\Export\Config\Reader',
            array(),
            array(),
            '',
            false
        );
        $this->_configScopeMock = $this->getMock('Magento\Framework\Config\CacheInterface');
    }

    /**
     * @param array $value
     * @param null|string $expected
     * @dataProvider getEntitiesDataProvider
     */
    public function testGetEntities($value, $expected)
    {
        $this->_configScopeMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->_cacheId
        )->will(
            $this->returnValue(false)
        );
        $this->_readerMock->expects($this->any())->method('read')->will($this->returnValue($value));
        $this->_model = new \Magento\ImportExport\Model\Export\Config(
            $this->_readerMock,
            $this->_configScopeMock,
            $this->_cacheId
        );
        $this->assertEquals($expected, $this->_model->getEntities('entities'));
    }

    public function getEntitiesDataProvider()
    {
        return array(
            'entities_key_exist' => array(array('entities' => 'value'), 'value'),
            'return_default_value' => array(array('key_one' => 'value'), null)
        );
    }

    /**
     * @param array $configData
     * @param string $entity
     * @param string[] $expectedResult
     * @dataProvider getEntityTypesDataProvider
     */
    public function testGetEntityTypes($configData, $entity, $expectedResult)
    {
        $this->_configScopeMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->_cacheId
        )->will(
            $this->returnValue(false)
        );
        $this->_readerMock->expects($this->any())->method('read')->will($this->returnValue($configData));
        $this->_model = new \Magento\ImportExport\Model\Export\Config(
            $this->_readerMock,
            $this->_configScopeMock,
            $this->_cacheId
        );
        $this->assertEquals($expectedResult, $this->_model->getEntityTypes($entity));
    }

    public function getEntityTypesDataProvider()
    {
        return [
            'valid type' => [
                [
                    'entities' => [
                        'catalog_product' => [
                            'types' => ['configurable', 'simple']
                        ]
                    ]
                ],
                'catalog_product',
                ['configurable', 'simple']
            ],
            'not existing entity' => [
                [
                    'entities' => [
                        'catalog_product' => [
                            'types' => ['configurable', 'simple']
                        ]
                    ]
                ],
                'not existing entity',
                []
            ],
        ];
    }

    /**
     * @param array $value
     * @param null|string $expected
     * @dataProvider getFileFormatsDataProvider
     */
    public function testGetFileFormats($value, $expected)
    {
        $this->_configScopeMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->_cacheId
        )->will(
            $this->returnValue(false)
        );
        $this->_readerMock->expects($this->any())->method('read')->will($this->returnValue($value));
        $this->_model = new \Magento\ImportExport\Model\Export\Config(
            $this->_readerMock,
            $this->_configScopeMock,
            $this->_cacheId
        );
        $this->assertEquals($expected, $this->_model->getFileFormats('fileFormats'));
    }

    public function getFileFormatsDataProvider()
    {
        return array(
            'fileFormats_key_exist' => array(array('fileFormats' => 'value'), 'value'),
            'return_default_value' => array(array('key_one' => 'value'), null)
        );
    }
}
