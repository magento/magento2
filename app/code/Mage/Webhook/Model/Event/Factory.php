<?php
/**
 * Creates new Mage_Webhook_Model_Event objects.
 *
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Event_Factory implements Magento_PubSub_Event_FactoryInterface
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /** @var Varien_Convert_Object  */
    private  $_arrayConverter;

    /**
     * Initialize the class
     *
     * @param Magento_ObjectManager $objectManager
     * @param Varien_Convert_Object $arrayConverter
     */
    public function __construct(
        Magento_ObjectManager $objectManager,
        Varien_Convert_Object $arrayConverter
    ) {
        $this->_objectManager = $objectManager;
        $this->_arrayConverter = $arrayConverter;
    }

    /**
     * Create event
     *
     * @param string $topic Topic on which to publish data
     * @param array $data Data to be published.  Should only contain primitives
     * @return Mage_Webhook_Model_Event
     */
    public function create($topic, $data)
    {
        return $this->_objectManager->create('Mage_Webhook_Model_Event', array(
            'data' => array(
                'topic' => $topic,
                'body_data' => serialize($this->_arrayConverter->convertDataToArray($data)),
                'status' => Magento_PubSub_EventInterface::READY_TO_SEND
            )
        ))->setDataChanges(true);
    }

    /**
     * Return the empty instance of Event
     *
     * @return Mage_Webhook_Model_Event
     */
    public function createEmpty()
    {
        return $this->_objectManager->create('Mage_Webhook_Model_Event');
    }
}
