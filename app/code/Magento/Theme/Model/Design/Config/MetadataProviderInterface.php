<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

/**
 * Interface \Magento\Theme\Model\Design\Config\MetadataProviderInterface
 *
 */
interface MetadataProviderInterface
{
    /**
     * Return design config field metadata as an array
     * Each array item consists metadata for one field. The key is a field name in UI XML configuration
     * The value is an array with metadata:
     *  - 'path' path in core_config_data
     *  - 'backend_model' field backend model
     *  - other optional parameters
     *
     * @return array
     */
    public function get();
}
