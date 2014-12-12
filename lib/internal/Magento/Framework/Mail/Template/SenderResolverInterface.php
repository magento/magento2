<?php
/**
 * Mail Sender Resolver interface
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
