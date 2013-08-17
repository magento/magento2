<?php
/**
 * Creates new messages
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
 * @package     Magento_Outbound
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Outbound_Message_Factory implements Magento_Outbound_Message_FactoryInterface
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Magento_Outbound_Formatter_Factory
     */
    private $_formatterFactory;

    /**
     * @var Magento_Outbound_Authentication_Factory
     */
    private $_authFactory;

    /**
     * initialize the class
     *
     * @param Magento_ObjectManager $objectManager
     * @param Magento_Outbound_Formatter_Factory $formatterFactory
     * @param Magento_Outbound_Authentication_Factory $authFactory
     */
    public function __construct(
        Magento_ObjectManager $objectManager,
        Magento_Outbound_Formatter_Factory $formatterFactory,
        Magento_Outbound_Authentication_Factory $authFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_formatterFactory = $formatterFactory;
        $this->_authFactory = $authFactory;
    }

    /**
     * Create a message for a given subscription and event
     *
     * @param Magento_Outbound_EndpointInterface $endpoint
     * @param Magento_PubSub_EventInterface      $event
     *
     * @return Magento_Outbound_Message
     */
    public function create(Magento_Outbound_EndpointInterface $endpoint, Magento_PubSub_EventInterface $event)
    {
        return $this->createByData($endpoint, $event->getTopic(), $event->getBodyData());
    }

    /**
     * Create a message for a given subscription and message data
     *
     * @param Magento_Outbound_EndpointInterface $endpoint
     * @param string                             $topic topic of the message
     * @param array                              $bodyData  body of the message
     *
     * @return Magento_Outbound_Message
     */
    public function createByData(Magento_Outbound_EndpointInterface $endpoint, $topic, array $bodyData)
    {
        // Format first since that should turn the body from an array into a string
        $formatter = $this->_formatterFactory->getFormatter($endpoint->getFormat());
        $headers = array(
            Magento_Outbound_Message_FactoryInterface::TOPIC_HEADER => $topic,
            Magento_Outbound_FormatterInterface::CONTENT_TYPE_HEADER => $formatter->getContentType(),
        );
        $formattedBody = $formatter->format($bodyData);

        $headers = array_merge(
            $headers,
            $this->_authFactory->getAuthentication($endpoint->getAuthenticationType())
                ->getSignatureHeaders($formattedBody, $endpoint->getUser())
        );

        return $this->_objectManager->create(
            'Magento_Outbound_Message',
            array(
                 'endpointUrl' => $endpoint->getEndpointUrl(),
                 'headers'     => $headers,
                 'body'        => $formattedBody,
                 'timeout'     => $endpoint->getTimeoutInSecs(),
            )
        );
    }
}