<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Message;

class ExceptionMessagePool
{
    /**
     * Key of instance is the exception format parameter
     *
     * @var ExceptionMessageInterface[]
     */
    private $messageConfigurationsMap = [];

    /**
     * @param ExceptionMessageInterface $defaultConfiguration
     * @param ExceptionMessageInterface[] $messageConfigurationsMap
     */
    public function __construct(
        ExceptionMessageInterface $defaultConfiguration,
        array $messageConfigurationsMap = []
    ) {
        $this->defaultConfiguration = $defaultConfiguration;
        $this->messageConfigurationsMap = $messageConfigurationsMap;
    }

    /**
     * Gets instance of a message exception message
     *
     * @param \Exception $exception
     * @return ExceptionMessageInterface|null
     */
    public function getMessageGenerator(\Exception $exception)
    {
        if (isset($this->messageConfigurationsMap[get_class($exception)])) {
            return $this->messageConfigurationsMap[get_class($exception)];
        }
        return $this->defaultConfiguration;
    }
}
