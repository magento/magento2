<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class MimeMessageInterfaceFactory
 */
class MimeMessageInterfaceFactory
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
        $instanceName = MimeMessageInterface::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Creates MimeMessageInterface instance with specified parameters
     *
     * @param array $data
     * @return MimeMessageInterface
     */
    public function create(array $data = []): MimeMessageInterface
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
