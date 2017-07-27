<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\ConfigInterface;

/**
 * @deprecated
 */
class CartItemProcessorsPool
{
    /**
     * @var CartItemProcessorInterface[]
     */
    private $cartItemProcessors = [];

    /**
     * @var ConfigInterface
     */
    private $objectManagerConfig;

    /**
     * @param ConfigInterface $objectManagerConfig
     * @deprecated
     */
    public function __construct(ConfigInterface $objectManagerConfig)
    {
        $this->objectManagerConfig = $objectManagerConfig;
    }

    /**
     * @return CartItemProcessorInterface[]
     * @deprecated
     */
    public function getCartItemProcessors()
    {
        if (!empty($this->cartItemProcessors)) {
            return $this->cartItemProcessors;
        }

        $typePreference = $this->objectManagerConfig->getPreference(Repository::class);
        $arguments = $this->objectManagerConfig->getArguments($typePreference);
        if (isset($arguments['cartItemProcessors'])) {
            // Workaround for compiled mode.
            $processors = isset($arguments['cartItemProcessors']['_vac_'])
                ? $arguments['cartItemProcessors']['_vac_']
                : $arguments['cartItemProcessors'];
            foreach ($processors as $name => $processor) {
                $className = isset($processor['instance']) ? $processor['instance'] : $processor['_i_'];
                $this->cartItemProcessors[$name] = ObjectManager::getInstance()->get($className);
            }
        }

        return $this->cartItemProcessors;
    }
}
