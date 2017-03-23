<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Control;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ButtonProviderFactory
 */
class ButtonProviderFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create Button Provider
     *
     * @param string $providerClass
     * @param array $arguments
     * @return ButtonProviderInterface
     */
    public function create($providerClass, array $arguments = [])
    {
        $object = $this->objectManager->create($providerClass, ['arguments' => $arguments]);
        if (!$object instanceof ButtonProviderInterface) {
            throw new \InvalidArgumentException(
                sprintf('"%s" must implement the interface ButtonProviderInterface.', $providerClass)
            );
        }

        return $object;
    }
}
