<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Model;

/**
 * Email from contact form
 *
 * @api
 */
interface MailInterface
{
    /**
     * Send email from contact form
     *
     * @param string $replyTo Reply-to email address
     * @param array $variables Email template variables
     * @return void
     */
    public function send($replyTo, array $variables);
}
