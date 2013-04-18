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
 * @package     Mage_Webhook
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Webhook_Model_Mapper_DefaultTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->_objectManager = Mage::getObjectManager();
    }

    public function testDefaultMapper()
    {
        $data = array(
            'objectA' => new Varien_Object(array('key1' => 'val1', 'key2' => 'val2')),
            'objectB' => new Varien_Object(array('keyB' => 'valueB'))
        );

        $this->_objectManager->get('Mage_Core_Model_Config');
        $mapper = Mage::getModel('Mage_Webhook_Model_Mapper_Factory', $this->_objectManager)
            ->getMapperFactory('default', $this->_objectManager->get('Mage_Core_Model_Config')
                ->getNode('global/webhook/mappings'))
            ->getMapper('some/topic', $data, $this->_objectManager->get('Mage_Core_Model_Config')
                ->getNode('global/webhook/mappings/default/options'));

        $this->assertEquals('some/topic', $mapper->getTopic());
        $this->assertEquals(array(Mage_Webhook_Model_Mapper_Default::TOPIC_HEADER => 'some/topic'),
            $mapper->getHeaders());

        $expectedData = array(
            'objectA' => array('key1' => 'val1', 'key2' => 'val2'),
            'objectB' => array('keyB' => 'valueB')
        );
        $this->assertEquals($expectedData, $mapper->getData());
    }

    protected function tearDown()
    {
        unset($this->_objectManager);
    }
}
