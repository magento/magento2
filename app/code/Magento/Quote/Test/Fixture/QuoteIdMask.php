<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Magento\TestFramework\Fixture\DataFixtureInterface;

/**
 * Persist quote id mask
 */
class QuoteIdMask implements DataFixtureInterface
{
    private const FIELD_CART_ID = 'cart_id';

    /**
     * @var QuoteIdMaskFactory
     */
    private QuoteIdMaskFactory $quoteIdMaskFactory;

    /**
     * @var QuoteIdMaskResourceModel
     */
    private QuoteIdMaskResourceModel $quoteIdMaskResourceModel;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResourceModel $quoteIdMaskResourceModel
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResourceModel $quoteIdMaskResourceModel
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        if (empty($data[self::FIELD_CART_ID])) {
            throw new InvalidArgumentException(__('"%field" is required', ['field' => self::FIELD_CART_ID]));
        }

        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $quoteIdMask->setQuoteId($data[self::FIELD_CART_ID]);
        $this->quoteIdMaskResourceModel->save($quoteIdMask);

        return $quoteIdMask;
    }
}
