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
namespace Magento\Outbound\Message;

class Factory implements \Magento\Outbound\Message\FactoryInterface
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Outbound\Formatter\Factory
     */
    private $_formatterFactory;

    /**
     * @var \Magento\Outbound\Authentication\Factory
     */
    private $_authFactory;

    /**
     * initialize the class
     *
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Outbound\Formatter\Factory $formatterFactory
     * @param \Magento\Outbound\Authentication\Factory $authFactory
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\Outbound\Formatter\Factory $formatterFactory,
        \Magento\Outbound\Authentication\Factory $authFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_formatterFactory = $formatterFactory;
        $this->_authFactory = $authFactory;
    }

    /**
     * Create a message for a given endpoint, topic and message data
     *
     * @param \Magento\Outbound\EndpointInterface $endpoint
     * @param string                             $topic topic of the message
     * @param array                              $bodyData  body of the message
     *
     * @return \Magento\Outbound\Message
     */
    public function create(\Magento\Outbound\EndpointInterface $endpoint, $topic, array $bodyData)
    {
        // Format first since that should turn the body from an array into a string
        $formatter = $this->_formatterFactory->getFormatter($endpoint->getFormat());
        $headers = array(
            \Magento\Outbound\Message\FactoryInterface::TOPIC_HEADER => $topic,
            \Magento\Outbound\FormatterInterface::CONTENT_TYPE_HEADER => $formatter->getContentType(),
        );
        $formattedBody = $formatter->format($bodyData);

        $headers = array_merge(
            $headers,
            $this->_authFactory->getAuthentication($endpoint->getAuthenticationType())
                ->getSignatureHeaders($formattedBody, $endpoint->getUser())
        );

        return $this->_objectManager->create(
            'Magento\Outbound\Message',
            array(
                 'endpointUrl' => $endpoint->getEndpointUrl(),
                 'headers'     => $headers,
                 'body'        => $formattedBody,
                 'timeout'     => $endpoint->getTimeoutInSecs(),
            )
        );
    }
}
