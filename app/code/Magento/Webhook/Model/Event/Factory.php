<?php
/**
 * Creates new \Magento\Webhook\Model\Event objects.
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Event;

class Factory implements \Magento\PubSub\Event\FactoryInterface
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /** @var \Magento\Convert\Object  */
    private  $_arrayConverter;

    /**
     * Initialize the class
     *
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Convert\Object $arrayConverter
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\Convert\Object $arrayConverter
    ) {
        $this->_objectManager = $objectManager;
        $this->_arrayConverter = $arrayConverter;
    }

    /**
     * Create event
     *
     * @param string $topic Topic on which to publish data
     * @param array $data Data to be published.  Should only contain primitives
     * @return \Magento\Webhook\Model\Event
     */
    public function create($topic, $data)
    {
        return $this->_objectManager->create('Magento\Webhook\Model\Event', array(
            'data' => array(
                'topic' => $topic,
                'body_data' => serialize($this->_arrayConverter->convertDataToArray($data))
            )
        ))->setDataChanges(true);
    }

    /**
     * Return the empty instance of Event
     *
     * @return \Magento\Webhook\Model\Event
     */
    public function createEmpty()
    {
        return $this->_objectManager->create('Magento\Webhook\Model\Event');
    }
}
