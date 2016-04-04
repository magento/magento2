<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Template;

/**
 * Mail Sender Resolver interface
 *
 * @api
 */
interface SenderResolverInterface
{
    /**
     * Resolve sender information. The $sender can be a string to identify which sender to lookup in the config and
     * return the name and email for. The $sender can be an array prefilled with the name and email key/value pairs.
     *
     * @throws \Magento\Framework\Exception\MailException
     * @param string|array $sender
     * @param int|null $scopeId
     * @return array an array with 'name' and 'email' key/value pairs
     */
    public function resolve($sender, $scopeId = null);
}
