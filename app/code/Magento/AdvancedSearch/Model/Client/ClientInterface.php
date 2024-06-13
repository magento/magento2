<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\AdvancedSearch\Model\Client;

/**
 * @api
 * @since 100.1.0
 */
interface ClientInterface
{
    /**
     * Validate connection params for search engine
     *
     * @return bool
     * @since 100.1.0
     */
    public function testConnection();
}
