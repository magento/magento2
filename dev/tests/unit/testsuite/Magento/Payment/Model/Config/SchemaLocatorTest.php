<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Payment\Model\Config\SchemaLocator
     */
    protected $model;

    const MODULE_DIR_PATH = '/path/to/payment/schema';

    public function setUp()
    {
        $moduleReader = $this->getMockBuilder(
            'Magento\Framework\Module\Dir\Reader'
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $moduleReader->expects($this->exactly(2))->method('getModuleDir')->with('etc', 'Magento_Payment')->will(
            $this->returnValue(self::MODULE_DIR_PATH)
        );
        $this->model = new SchemaLocator($moduleReader);
    }

    public function testGetSchema()
    {
        $this->assertEquals(
            self::MODULE_DIR_PATH . '/' . SchemaLocator::MERGED_CONFIG_SCHEMA,
            $this->model->getSchema()
        );
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals(
            self::MODULE_DIR_PATH . '/' . SchemaLocator::PER_FILE_VALIDATION_SCHEMA,
            $this->model->getPerFileSchema()
        );
    }
}
