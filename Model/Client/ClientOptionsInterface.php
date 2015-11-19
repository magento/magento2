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
     * @return array
     */
    public function prepareClientOptions($options = []);

    /**
     * Check if third party engine is selected and active
     *
     * @return bool
     */
    public function isThirdPartyEngineAvailable();
}
