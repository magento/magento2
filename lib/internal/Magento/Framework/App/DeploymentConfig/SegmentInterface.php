<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

/**
 * An abstraction for deployment configuration "segments"
 */
interface SegmentInterface
{
    /**
     * Gets segment key of deployment configuration
     *
     * @return string
     */
    public function getKey();

    /**
     * Gets the segment data
     *
     * @return mixed
     */
    public function getData();
}
