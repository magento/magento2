<?php
/**
 * Mail Sender Resolver interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Template;

interface SenderResolverInterface
{
    /**
     * Resolve sender data
     * @throws \Magento\Framework\Mail\Exception
     * @param string|array $sender
     * @param int|null $scopeId
     * @return array
     */
    public function resolve($sender, $scopeId = null);
}
