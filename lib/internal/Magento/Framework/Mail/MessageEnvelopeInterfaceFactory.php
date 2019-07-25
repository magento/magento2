<?php
/**
 * Mail Template Transport Builder
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class MessageEnvelopeInterfaceFactory
 */
class MessageEnvelopeInterfaceFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $instanceName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = MessageEnvelopeInterface::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create MessageEnvelopeInterface instance with specified parameters
     *
     * @param array $data
     * @return MessageEnvelopeInterface
     */
    public function create(array $data = []): MessageEnvelopeInterface
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
