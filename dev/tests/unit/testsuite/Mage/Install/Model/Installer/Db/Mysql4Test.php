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
 * @package     Mage_Install
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Install_Model_Installer_Db_Mysql4Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test possible ways of declaring InnoDB engine by MySQL
     *
     * @dataProvider possibleEngines
     * @param array $supportedEngines
     * @param $expectedResult
     * @return void
     */
    public function testSupportEngine(array $supportedEngines, $expectedResult)
    {
        $connectionMock = $this->getMock('Varien_Db_Adapter_Interface');
        $resourceMock = $this->getMock('Mage_Core_Model_Resource', array('createConnection'), array(), '', false);
        $resourceMock->expects($this->once())->method('createConnection')->will($this->returnValue($connectionMock));

        $connectionMock->expects($this->once())->method('fetchPairs')->will($this->returnValue($supportedEngines));

        $installer = new Mage_Install_Model_Installer_Db_Mysql4($resourceMock);
        $this->assertEquals($expectedResult, $installer->supportEngine());
    }

    /**
     * Data provider for returned engines from mysql and expectations.
     * @return array
     */
    public function possibleEngines()
    {
        return array(
            array(array('InnoDB' => 'DEFAULT'),  true),
            array(array('InnoDB' => 'YES'),      true),
            array(array('wrongEngine' => '123'), false)
        );
    }
}
