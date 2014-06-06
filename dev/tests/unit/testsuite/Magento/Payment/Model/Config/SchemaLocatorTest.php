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
