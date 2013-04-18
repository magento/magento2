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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Paypal_Model_Report_SettlementTest extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoDbIsolation enabled
     */
    public function testFetchAndSave()
    {
        /** @var $model Mage_Paypal_Model_Report_Settlement; */
        $model = Mage::getModel('Mage_Paypal_Model_Report_Settlement');
        $connection = $this->getMock('Varien_Io_Sftp', array('rawls', 'read'), array(), '', false);
        $filename = 'STL-00000000.00.abc.CSV';
        $connection->expects($this->once())->method('rawls')->will($this->returnValue(array($filename => array())));
        $connection->expects($this->once())->method('read')->with($filename, $this->anything());
        $model->fetchAndSave($connection);
    }

    /**
     * @param array $config
     * @expectedException InvalidArgumentException
     * @dataProvider createConnectionExceptionDataProvider
     */
    public function testCreateConnectionException($config)
    {
        Mage_Paypal_Model_Report_Settlement::createConnection($config);
    }

    /**
     * @return array
     */
    public function createConnectionExceptionDataProvider()
    {
        return array(
            array(array()),
            array(array('username' => 'test', 'password' => 'test', 'path' => '/')),
            array(array('hostname' => 'example.com', 'password' => 'test', 'path' => '/')),
            array(array('hostname' => 'example.com', 'username' => 'test', 'path' => '/')),
            array(array('hostname' => 'example.com', 'username' => 'test', 'password' => 'test')),
        );
    }
}
