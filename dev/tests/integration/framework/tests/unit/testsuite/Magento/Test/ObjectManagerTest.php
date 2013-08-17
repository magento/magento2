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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Magento_ObjectManager_Test
 */
class Magento_Test_ObjectManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Expected instance manager parametrized cache after clear
     *
     * @var array
     */
    protected $_instanceCache = array(
        'hashShort' => array(),
        'hashLong'  => array()
    );

    public function testClearCache()
    {
        $resource = new stdClass;
        $instanceConfig = new Magento_Test_ObjectManager_Config();
        $primaryConfig = $this->getMock('Mage_Core_Model_Config_Primary', array(), array(), '', false);
        $dirs = $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false);
        $primaryConfig->expects($this->any())->method('getDirectories')->will($this->returnValue($dirs));
        $model = new Magento_Test_ObjectManager($primaryConfig, $instanceConfig);
        $model->addSharedInstance($resource, 'Mage_Core_Model_Resource');
        $instance1 = $model->get('Magento_Test_Request');

        $this->assertSame($instance1, $model->get('Magento_Test_Request'));
        $this->assertSame($model, $model->clearCache());
        $this->assertSame($model, $model->get('Magento_ObjectManager'));
        $this->assertSame($resource, $model->get('Mage_Core_Model_Resource'));
        $this->assertNotSame($instance1, $model->get('Magento_Test_Request'));
    }
}
