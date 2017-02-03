<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ProviderFactory
 *
 * Factory for report providers
 */
class ProviderFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $providerName
     * @return object
     */
    public function create($providerName)
    {
        return $this->objectManager->get($providerName);
    }
}
