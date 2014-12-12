<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
