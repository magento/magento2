<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Configuration;

/**
 * Value object for parsed channel configuration
 */
class ParsedChannelConfiguration
{
    /**
     * @var array
     */
    private $handlers;

    /**
     * @var array
     */
    private $processors;

    /**
     * ParsedChannelConfiguration constructor.
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(array $handlers, array $processors)
    {
        $this->handlers = $handlers;
        $this->processors = $processors;
    }

    /**
     * @return array
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * @return array
     */
    public function getProcessors(): array
    {
        return $this->processors;
    }
}
