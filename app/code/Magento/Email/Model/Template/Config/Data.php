<?php
/**
 * Email templates configuration data container. Provides email templates configuration data.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template\Config;

class Data extends \Magento\Framework\Config\Data
{
    /**
     * @param \Magento\Email\Model\Template\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     */
    public function __construct(
        \Magento\Email\Model\Template\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache
    ) {
        parent::__construct($reader, $cache, 'email_templates');
    }
}
