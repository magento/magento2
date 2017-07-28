<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Framework\MessageQueue\MergerFactory
 *
 * @since 2.0.0
 */
class MergerFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @var string[]
     * @since 2.0.0
     */
    private $mergers;

    /**
     * MergerFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param string[] $mergers
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager, $mergers)
    {
        $this->objectManager = $objectManager;
        $this->mergers = $mergers;
    }

    /**
     * @param string $consumerName
     * @return MergerInterface
     * @throws \LogicException
     * @since 2.0.0
     */
    public function create($consumerName)
    {
        if (!isset($this->mergers[$consumerName])) {
            throw new \LogicException("Not found merger for consumer name '{$consumerName}'");
        }

        $mergerClassName = $this->mergers[$consumerName];
        $merger = $this->objectManager->get($mergerClassName);

        if (!$merger instanceof MergerInterface) {
            $mergerInterfaceName = \Magento\Framework\MessageQueue\MergerInterface::class;
            throw new \LogicException(
                "Merger '{$mergerClassName}' for consumer name '{$consumerName}' " .
                "does not implement interface '{$mergerInterfaceName}'"
            );
        }

        return $merger;
    }
}
