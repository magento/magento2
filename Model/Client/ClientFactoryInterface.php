<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

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
