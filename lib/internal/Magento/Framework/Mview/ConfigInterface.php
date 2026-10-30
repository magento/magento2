<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview;

/**
 * Interface \Magento\Framework\Mview\ConfigInterface
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Get views list
     *
     * @return array[]
     */
    public function getViews();

    /**
     * Get view by ID
     *
     * @param string $viewId
     * @return array
     */
    public function getView($viewId);
}
