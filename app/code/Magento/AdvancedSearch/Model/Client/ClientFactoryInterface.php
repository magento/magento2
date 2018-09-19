<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

/**
 * @api
 * @since 100.1.0
 */
interface ClientFactoryInterface
{
    /**
     * Return search client
     *
     * @param array $options
     * @return ClientInterface
     * @since 100.1.0
     */
    public function create(array $options = []);
}
