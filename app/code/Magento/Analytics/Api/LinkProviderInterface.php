<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Analytics\Api;

/**
 * Provides link to file with collected report data.
 *
 * @api
 */
interface LinkProviderInterface
{
    /**
     * Retrieve link
     *
     * @return \Magento\Analytics\Api\Data\LinkInterface
     */
    public function get();
}
