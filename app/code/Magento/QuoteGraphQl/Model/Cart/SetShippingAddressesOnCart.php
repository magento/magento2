<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use InvalidArgumentException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * Set shipping addresses for shopping cart processors chain
 */
class SetShippingAddressesOnCart implements SetShippingAddressesOnCartInterface
{
    /**
     * @var SetShippingAddressesOnCartInterface[]
     */
    private $shippingAddressProcessors;

    /**
     * @param array $shippingAddressProcessors
     */
    public function __construct(
        array $shippingAddressProcessors
    ) {
        $this->shippingAddressProcessors = $shippingAddressProcessors;
    }

    /**
     * @inheritdoc
     */
    public function execute(ContextInterface $context, int $cartId, array $shippingAddresses): void
    {
        foreach ($this->shippingAddressProcessors as $shippingAddressProcessor) {
            if (!$shippingAddressProcessor instanceof SetShippingAddressesOnCartInterface) {
                throw new InvalidArgumentException(
                    get_class($shippingAddressProcessor) .
                    ' is not instance of \Magento\QuoteGraphQl\Model\Cart\SetShippingAddressesOnCartInterface.'
                );
            }

            $shippingAddressProcessor->execute($context, $cartId, $shippingAddresses);
        }
    }
}
