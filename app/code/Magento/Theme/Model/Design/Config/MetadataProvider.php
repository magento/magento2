<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

class MetadataProvider implements MetadataProviderInterface
{
    /**
     * @var array
     */
    protected $metadata;

    /**
     * @param array $metadata
     */
    public function __construct(array $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function get()
    {
        return $this->metadata;
    }
}
