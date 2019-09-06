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
