<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Api;

/**
 * Provides link to file with collected report data.
 */
interface LinkProviderInterface
{
    /**
     * @return \Magento\Analytics\Api\Data\LinkInterface
     */
    public function get();
}
