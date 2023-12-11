<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\ObjectManagerInterface;

class MergerFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string[]
     */
    private $mergers;

    /**
     * MergerFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param string[] $mergers
     */
    public function __construct(ObjectManagerInterface $objectManager, $mergers = [])
    {
        $this->objectManager = $objectManager;
        $this->mergers = $mergers;
    }

    /**
     * @param string $consumerName
     * @return MergerInterface
     * @throws \LogicException
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
