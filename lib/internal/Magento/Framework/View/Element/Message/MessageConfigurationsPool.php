<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message;

/**
 * Class \Magento\Framework\View\Element\Message\MessageConfigurationsPool
 *
 * @since 2.0.0
 */
class MessageConfigurationsPool
{
    /**
     * @var array
     * @since 2.0.0
     */
    private $configurationsMap;

    /**
     * Expected input:
     * ['message_identifier' => ['renderer' => '{renderer_code}', 'data' => []], ...]
     *
     * @param array $configurationsMap
     * @since 2.0.0
     */
    public function __construct(
        array $configurationsMap = []
    ) {
        array_walk(
            $configurationsMap,
            function (array &$configuration) {
                if (!isset($configuration['renderer'])
                    || !is_string($configuration['renderer'])
                ) {
                    throw new \InvalidArgumentException('Renderer should be defined.');
                }

                if (isset($configuration['data'])
                    && !is_array($configuration['data'])
                ) {
                    throw new \InvalidArgumentException('Data should be of array type.');
                }

                if (!isset($configuration['data'])) {
                    $configuration['data'] = [];
                }
            }
        );

        $this->configurationsMap = $configurationsMap;
    }

    /**
     * Returns message configuration as
     * ['message_identifier' => ['renderer' => '{renderer_code}', 'data' => []], ...]
     *
     * @param string $identifier
     * @return null|array
     * @since 2.0.0
     */
    public function getMessageConfiguration($identifier)
    {
        return !isset($this->configurationsMap[$identifier])
            ? null
            : $this->configurationsMap[$identifier];
    }
}
