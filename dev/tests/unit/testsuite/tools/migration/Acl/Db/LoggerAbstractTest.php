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
 * @category    Tools
 * @package     unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once realpath(dirname(__FILE__) . '/../../../../../../../') . '/tools/migration/Acl/Db/LoggerAbstract.php';

class Tools_Migration_Acl_Db_LoggerAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Tools_Migration_Acl_Db_LoggerAbstract
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = $this->getMockForAbstractClass('Tools_Migration_Acl_Db_LoggerAbstract');
    }

    public function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @covers Tools_Migration_Acl_Db_LoggerAbstract::add()
     * @covers Tools_Migration_Acl_Db_LoggerAbstract::__toString()
     */
    public function testToString()
    {
        $this->_model->add('key1', 'key2', 3); // mapped item
        $this->_model->add('key2', null, false); // not mapped item
        $this->_model->add(null, 'Some_Module::acl_resource', false); //item in actual format

        $expected = 'Mapped items count: 1' . PHP_EOL 
            . 'Not mapped items count: 1' . PHP_EOL
            . 'Items in actual format count: 1' . PHP_EOL
            . '------------------------------' . PHP_EOL
            . 'Mapped items:' . PHP_EOL
            . 'key1 => key2 :: Count updated rules: 3' . PHP_EOL
            . '------------------------------' . PHP_EOL
            . 'Not mapped items:' . PHP_EOL
            . 'key2' . PHP_EOL
            . '------------------------------' . PHP_EOL
            . 'Items in actual format:' . PHP_EOL
            . 'Some_Module::acl_resource' . PHP_EOL
            . '------------------------------' . PHP_EOL;

        $this->assertEquals($expected, (string)$this->_model);
    }
}

