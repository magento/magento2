<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Api;

/**
 * Provides link to file with collected report data.
 * @since 2.2.0
 */
interface LinkProviderInterface
{
    /**
     * @return \Magento\Analytics\Api\Data\LinkInterface
     * @since 2.2.0
     */
    public function get();
}
