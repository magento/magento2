<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Source;

/**
 * Configurable (via di.xml) pool of available sources of static files eligible for deployment
 * @since 2.2.0
 */
class SourcePool
{
    /**
     * Source objects
     *
     * @var SourceInterface[]
     * @since 2.2.0
     */
    private $sources;

    /**
     * SourcePool constructor.
     * @param array $sources
     * @since 2.2.0
     */
    public function __construct(array $sources)
    {
        $this->sources = $sources;
    }

    /**
     * Retrieve static files sources
     *
     * @return SourceInterface[]
     * @since 2.2.0
     */
    public function getAll()
    {
        return $this->sources;
    }

    /**
     * Retrieve source
     *
     * @param string $name
     * @return SourceInterface|null
     * @since 2.2.0
     */
    public function getSource($name)
    {
        return isset($this->sources[$name]) ? $this->sources[$name] : null;
    }
}
