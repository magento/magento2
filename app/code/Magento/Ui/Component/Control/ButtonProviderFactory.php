<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Control;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ButtonProviderFactory
 * @package Magento\Ui\DataProvider
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
