<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\Message\Renderer;

class MessageConfigurationsPool
{
    /**
     * Key of instance is the exception format parameter
     *
     * @var MessageConfigurationInterface[]
     */
    private $messageConfigurationsMap = [];

    /**
     * @param MessageConfigurationInterface $defaultConfiguration
     * @param MessageConfigurationInterface[] $messageConfigurationsMap
     */
    public function __construct(
        MessageConfigurationInterface $defaultConfiguration,
        array $messageConfigurationsMap = []
    ) {
        $this->defaultConfiguration = $defaultConfiguration;
        $this->messageConfigurationsMap = $messageConfigurationsMap;
    }

    /**
     * Gets instance of a message configuration
     *
     * @param \Exception $exception
     * @return MessageConfigurationInterface|null
     */
    public function getMessageGenerator(\Exception $exception)
    {
        if (isset($this->messageConfigurationsMap[get_class($exception)])) {
            return $this->messageConfigurationsMap[get_class($exception)];
        }
        return $this->defaultConfiguration;
    }
}
