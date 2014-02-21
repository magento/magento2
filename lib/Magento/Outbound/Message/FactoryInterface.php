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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Outbound\Message;

interface FactoryInterface
{

    /** Topic header */
    const TOPIC_HEADER = 'Magento-Topic';

    /**
     * Create a message for a given endpoint, topic and message data
     *
     * @param \Magento\Outbound\EndpointInterface $endpoint
     * @param string $topic topic of the message
     * @param array $bodyData body of the message
     * @return \Magento\Outbound\Message
     */
    public function create(\Magento\Outbound\EndpointInterface $endpoint, $topic, array $bodyData);
}
