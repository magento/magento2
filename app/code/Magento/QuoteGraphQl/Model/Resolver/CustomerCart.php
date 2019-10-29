<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;

/**
 * @inheritdoc
 */
class CustomerCart implements ResolverInterface
{
    /**
     * @var CreateEmptyCartForCustomer
     */
    private $createEmptyCartForCustomer;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @param CreateEmptyCartForCustomer $createEmptyCartForCustomer
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param HttpContext $httpContext
     * @param GetCartForUser $getCartForUser
     */
    public function __construct(
        CreateEmptyCartForCustomer $createEmptyCartForCustomer,
        HttpContext $httpContext,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        GetCartForUser $getCartForUser
    ) {
        $this->createEmptyCartForCustomer = $createEmptyCartForCustomer;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->httpContext = $httpContext;
         $this->getCartForUser = $getCartForUser;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $customerId = $context->getUserId();
        $predefinedMaskedQuoteId = null;
        $maskedCartId = $this->createEmptyCartForCustomer->execute($customerId, $predefinedMaskedQuoteId);

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $customerId, $storeId);

        if (empty($cart)){
            $maskedCartId = $this->createEmptyCartForCustomer->execute($customerId, $predefinedMaskedQuoteId);
        }
        return [
            'model' => $cart,
        ];
    }
}
