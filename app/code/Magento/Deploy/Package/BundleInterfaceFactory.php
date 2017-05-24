<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Package;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Factory class for Package Bundle Interface
 *
 * @see Bundle
 */
class BundleInterfaceFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * BundleFactory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $type
     */
    public function __construct(ObjectManagerInterface $objectManager, $type = BundleInterface::class)
    {
        $this->objectManager = $objectManager;
        $this->type = $type;
    }

    /**
     * Create new instance of Package Bundle object
     *
     * Since returned type can be configured via DI configuration, the method does check type of created object
     * and throws exception if that object is not instance of \Magento\Deploy\Package\BundleInterface
     *
     * @param array $arguments
     * @return BundleInterface
     * @throws LocalizedException
     */
    public function create(array $arguments)
    {
        $package = $this->objectManager->create($this->type, $arguments);
        if (!$package instanceof BundleInterface) {
            throw new LocalizedException(
                new Phrase("Wrong type specified: '%1'", [$this->type])
            );
        }
        return $package;
    }
}
