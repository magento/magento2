<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

interface ClientInterface
{
    /**
     * Ping search engine
     * 
     * @return array
     */
    public function ping();
}
