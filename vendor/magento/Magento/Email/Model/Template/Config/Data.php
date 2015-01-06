<?php
/**
 * Email templates configuration data container. Provides email templates configuration data.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
