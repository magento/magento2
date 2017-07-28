<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\ConfigInterface;

/**
 * @deprecated 2.1.0
 * @since 2.1.0
 */
class CartItemProcessorsPool
{
    /**
     * @var CartItemProcessorInterface[]
     * @since 2.1.0
     */
    private $cartItemProcessors = [];

    /**
     * @var ConfigInterface
     * @since 2.1.0
     */
    private $objectManagerConfig;

    /**
     * @param ConfigInterface $objectManagerConfig
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    public function __construct(ConfigInterface $objectManagerConfig)
    {
        $this->objectManagerConfig = $objectManagerConfig;
    }

    /**
     * @return CartItemProcessorInterface[]
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    public function getCartItemProcessors()
    {
        if (!empty($this->cartItemProcessors)) {
            return $this->cartItemProcessors;
        }

        $arguments = $this->objectManagerConfig->getArguments(\Magento\Quote\Model\Quote\Item\Repository::class);
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
