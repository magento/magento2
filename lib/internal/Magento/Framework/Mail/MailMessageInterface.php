<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

/**
 * Mail Message interface
 *
 * @api
 */
interface MailMessageInterface extends MessageInterface
{
    /**
     * @param string $html
     * @return $this
     */
    public function setBodyHtml($html);

    /**
     * @param string $text
     * @return $this
     */
    public function setBodyText($text);

    /**
     * @return string
     */
    public function getBodyText();

    /**
     * @return string
     */
    public function getBodyHtml();

    /**
     * Get message source code
     *
     * @return string
     */
    public function getRawMessage();
}
