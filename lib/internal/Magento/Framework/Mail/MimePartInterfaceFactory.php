<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class MimePartInterfaceFactory
 */
class MimePartInterfaceFactory
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
        $instanceName = MimePartInterface::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create MimePartInterface instance with specified parameters
     *
     * @param array $data
     * @return MimePartInterface
     */
    public function create(array $data = []): MimePartInterface
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
