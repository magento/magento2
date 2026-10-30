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
interface ClientOptionsInterface
{
    /**
     * Return search client options
     *
     * @param array $options
     * @return array
     * @since 100.1.0
     */
    public function prepareClientOptions($options = []);
}
