<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Api;

/**
 * Interface for sending contact form data.
 * @api
 */
interface ContactInterface
{
    /**
     * Send an email to the contact person with contact form data
     *
     * @param string $name
     * @param string $email
     * @param string $telephone
     * @param string $comment
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function send($name, $email, $telephone = null, $comment);

}
