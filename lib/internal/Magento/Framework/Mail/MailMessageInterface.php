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
 * @since 102.0.0
 */
interface MailMessageInterface extends MessageInterface
{
    /**
     * Set mail message body in HTML format.
     *
     * @param string $html
     * @return $this
     * @since 102.0.0
     */
    public function setBodyHtml($html);

    /**
     * Set mail message body in text format.
     *
     * @param string $text
     * @return $this
     * @since 102.0.0
     */
    public function setBodyText($text);

    /**
     * Get message source code.
     *
     * @return string
     * @since 102.0.0
     */
    public function getRawMessage();
}
