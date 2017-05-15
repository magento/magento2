<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

/**
 * @api
 */
interface ClientFactoryInterface
{
    /**
     * Return search client
     *
     * @param array $options
     * @return ClientInterface
     */
    public function create(array $options = []);
}
