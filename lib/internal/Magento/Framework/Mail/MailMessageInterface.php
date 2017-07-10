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
     * Set mail message body in HTML format.
     *
     * @param string $html
     * @return $this
     */
    public function setBodyHtml($html);

    /**
     * Set mail message body in text format.
     *
     * @param string $text
     * @return $this
     */
    public function setBodyText($text);

    /**
     * Get message source code.
     *
     * @return string
     */
    public function getRawMessage();
}
