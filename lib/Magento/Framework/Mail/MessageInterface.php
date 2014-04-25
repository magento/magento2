<?php
/**
 * Mail Message interface
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Mail;

interface MessageInterface
{
    /**
     * Types of message
     */
    const TYPE_TEXT = 'text/plain';

    const TYPE_HTML = 'text/html';

    /**
     * Set message subject
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject);

    /**
     * Get message subject
     *
     * @return string
     */
    public function getSubject();

    /**
     * Set message body
     *
     * @param mixed $body
     * @return $this
     */
    public function setBody($body);

    /**
     * Get message body
     *
     * @return mixed
     */
    public function getBody();

    /**
     * Set from address
     *
     * @param string|array $fromAddress
     * @return $this
     */
    public function setFrom($fromAddress);

    /**
     * Add to address
     *
     * @param string|array $toAddress
     * @return $this
     */
    public function addTo($toAddress);

    /**
     * Add cc address
     *
     * @param string|array $ccAddress
     * @return $this
     */
    public function addCc($ccAddress);

    /**
     * Add bcc address
     *
     * @param string|array $bccAddress
     * @return $this
     */
    public function addBcc($bccAddress);

    /**
     * Set reply-to address
     *
     * @param string|array $replyToAddress
     * @return $this
     */
    public function setReplyTo($replyToAddress);

    /**
     * Set message type
     *
     * @param string $type
     * @return $this
     */
    public function setMessageType($type);
}
