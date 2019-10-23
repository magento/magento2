<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use \Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;

/**
 * Configurable Product validate options observer
 */
class ValidateOptionsObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var ConfigurableProduct
     */
    private $configurableProduct;

    /**
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param ConfigurableProduct $configurableProduct
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        ConfigurableProduct $configurableProduct
    ) {
        $this->serializer = $serializer;
        $this->configurableProduct = $configurableProduct;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $quoteItem = $observer->getEvent()->getQuoteItem();
        if ($quoteItem->getProductType() == ConfigurableProduct::TYPE_CODE) {
            $product = $observer->getEvent()->getProduct();

            $option = $product->getCustomOption('info_buyRequest');
            if ($option instanceof \Magento\Quote\Model\Quote\Item\Option) {
                $buyRequest = new \Magento\Framework\DataObject($this->serializer->unserialize($option->getValue()));
                $attributes = $buyRequest->getSuperAttribute();

                $subProduct = $this->configurableProduct->getProductByAttributes($attributes, $product);
                if (!$subProduct) {
                    $quoteItem->setHasConfigurationUnavailableError(true);
                }
            }
        }
    }
}
