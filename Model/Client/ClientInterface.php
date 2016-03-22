<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

interface ClientInterface
{
    /**
     * Validate connection params for search engine
     * 
     * @return bool
     */
    public function testConnection();
}
