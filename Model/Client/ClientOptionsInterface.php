<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

interface ClientOptionsInterface
{
    /**
     * Return search client options
     *
     * @param array $options
     * @return mixed
     */
    public function prepareClientOptions($options = []);
}
