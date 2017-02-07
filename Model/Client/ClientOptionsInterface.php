<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

interface ClientOptionsInterface
{
    /**
     * Return search client options
     *
     * @param array $options
     * @return array
     */
    public function prepareClientOptions($options = []);
}
