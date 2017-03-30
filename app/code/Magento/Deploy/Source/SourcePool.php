<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Source;

/**
 * Class SourcePool
 */
class SourcePool
{
    /**
     * Source objects
     *
     * @var SourceInterface[]
     */
    private $sources;

    /**
     * SourcePool constructor.
     * @param array $sources
     */
    public function __construct(array $sources)
    {
        $this->sources = $sources;
    }

    /**
     * Retrieve static files sources
     *
     * @return SourceInterface[]
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
     */
    public function getSource($name)
    {
        return isset($this->sources[$name]) ? $this->sources[$name] : null;
    }
}
