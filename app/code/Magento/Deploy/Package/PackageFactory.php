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
 * Factory class for Package object
 *
 * @see Package
 */
class PackageFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $type;

    /**
     * PackageFactory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $type
     */
    public function __construct(ObjectManagerInterface $objectManager, $type = Package::class)
    {
        $this->objectManager = $objectManager;
        $this->type = $type;
    }

    /**
     * Create new instance of Package object
     *
     * Since returned type can be configured via DI configuration, the method does check type of created object
     * and throws exception if that instance is not successor of \Magento\Deploy\Package
     *
     * @param array $arguments
     * @return Package
     * @throws LocalizedException
     */
    public function create(array $arguments)
    {
        $package = $this->objectManager->create($this->type, $arguments);
        if (!$package instanceof Package) {
            throw new LocalizedException(
                new Phrase("Wrong type specified: '%1'", [$this->type])
            );
        }
        return $package;
    }
}
