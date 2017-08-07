<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

/**
 * Class \Magento\Theme\Model\Design\Config\MetadataProvider
 *
 * @since 2.1.0
 */
class MetadataProvider implements MetadataProviderInterface
{
    /**
     * @var array
     * @since 2.1.0
     */
    protected $metadata;

    /**
     * @param array $metadata
     * @since 2.1.0
     */
    public function __construct(array $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @inheritdoc
     *
     * @return array
     * @since 2.1.0
     */
    public function get()
    {
        return $this->metadata;
    }
}
