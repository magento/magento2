<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Plugin\Model\Message;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Include related product names in add-to-cart success messages from the product page.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AddRelatedProductsToSuccessMessagePlugin
{
    private const ADD_TO_CART_ACTION = 'checkout_cart_add';

    private const ADD_TO_CART_SUCCESS_MESSAGE = 'addCartSuccessMessage';

    private const MAX_CART_ITEMS_FOR_MESSAGE = 20;

    /**
     * @var HttpRequest
     */
    private HttpRequest $request;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @param HttpRequest $request
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        HttpRequest $request,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Include related product names in add-to-cart success message.
     *
     * @param ManagerInterface $subject
     * @param string $identifier
     * @param array $data
     * @param string|null $group
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddComplexSuccessMessage(
        ManagerInterface $subject,
        $identifier,
        array $data = [],
        $group = null
    ): ?array {
        if ($identifier !== self::ADD_TO_CART_SUCCESS_MESSAGE || !$this->shouldModifyMessage()) {
            return null;
        }

        $productList = $this->buildProductList((string)($data['product_name'] ?? ''));
        if ($productList === null) {
            return null;
        }

        $data['product_name'] = $productList;

        return [$identifier, $data, $group];
    }

    /**
     * Include related product names in add-to-cart success message.
     *
     * @param ManagerInterface $subject
     * @param Phrase|string $message
     * @param string|null $group
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddSuccessMessage(
        ManagerInterface $subject,
        $message,
        $group = null
    ): ?array {
        if (!$message instanceof Phrase
            || $message->getText() !== 'You added %1 to your shopping cart.'
            || !$this->shouldModifyMessage()
        ) {
            return null;
        }

        $arguments = $message->getArguments();
        if (count($arguments) !== 1) {
            return null;
        }

        $productList = $this->buildProductList((string)$arguments[0]);
        if ($productList === null) {
            return null;
        }

        $arguments[0] = $productList;

        return [new Phrase('You added %1 to your shopping cart.', $arguments), $group];
    }

    /**
     * Check if success message should include related products.
     *
     * @return bool
     */
    private function shouldModifyMessage(): bool
    {
        if ($this->getRelatedProductIds() === []) {
            return false;
        }

        if ($this->request->getFullActionName() !== self::ADD_TO_CART_ACTION) {
            return false;
        }

        return count($this->checkoutSession->getQuote()->getAllVisibleItems())
            <= self::MAX_CART_ITEMS_FOR_MESSAGE;
    }

    /**
     * Get related product IDs from request
     *
     * @return int[]
     */
    private function getRelatedProductIds(): array
    {
        $related = $this->request->getParam('related_product');
        if (!is_scalar($related) || (string)$related === '') {
            return [];
        }

        $ids = [];
        foreach (explode(',', (string)$related) as $relatedProductId) {
            $relatedProductId = (int)$relatedProductId;
            if ($relatedProductId > 0) {
                $ids[] = $relatedProductId;
            }
        }

        return $ids;
    }

    /**
     * Build formatted product name list with related products.
     *
     * @param string $mainProductName
     * @return string|null
     */
    private function buildProductList(string $mainProductName): ?string
    {
        $relatedProductIds = $this->getRelatedProductIds();
        if ($relatedProductIds === []) {
            return null;
        }

        $productNames = [$mainProductName];
        $storeId = (int)$this->storeManager->getStore()->getId();

        foreach ($relatedProductIds as $relatedProductId) {
            try {
                $productNames[] = $this->productRepository
                    ->getById($relatedProductId, false, $storeId)
                    ->getName();
            } catch (NoSuchEntityException $exception) {
                continue;
            }
        }

        if (count($productNames) < 2) {
            return null;
        }

        if (count($productNames) === 2) {
            return (string)__('%1 and %2', $productNames[0], $productNames[1]);
        }

        $lastProductName = array_pop($productNames);

        return (string)__('%1 and %2', implode(', ', $productNames), $lastProductName);
    }
}
