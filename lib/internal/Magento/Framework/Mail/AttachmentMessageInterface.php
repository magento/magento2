<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

/**
 * Mail Extended Message interface
 *
 * @api
 */
interface AttachmentMessageInterface extends MessageInterface
{
    /**
     * Add a joined file to the email.
     * Attachment is automatically added to the mail object after creation. The
     * attachment object is returned to allow for further manipulation.
     *
     * @param string $body
     * @param string $mimeType
     * @param string $disposition
     * @param string $encoding
     * @param string $filename
     * @return mixed
     */
    public function createAttachment($body, $mimeType, $disposition, $encoding, $filename);
}
