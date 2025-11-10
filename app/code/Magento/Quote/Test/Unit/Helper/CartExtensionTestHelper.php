<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Api\Data\CartExtension;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Test helper for CartExtension to provide setShippingAssignments() for unit tests.
 */
class CartExtensionTestHelper extends CartExtension
{
    /** @var array */
    private array $testData = [];

    /**
     * @var NegotiableQuoteInterface
     */
    private $negotiableQuote;

    /**
     * @var mixed
     */
    private $companyId;

    /**
     * @var mixed
     */
    private $negotiableQuoteItem;

    /**
     * Constructor to optionally set negotiable quote
     *
     * @param NegotiableQuoteInterface|null $negotiableQuote
     */
    public function __construct($negotiableQuote = null)
    {
        if ($negotiableQuote !== null) {
            $this->negotiableQuote = $negotiableQuote;
        }
    }

    /**
     * Set shipping assignments for tests.
     *
     * @param array $shippingAssignments
     * @return $this
     */
    public function setShippingAssignments($shippingAssignments)
    {
        $this->testData['shipping_assignments'] = $shippingAssignments;
        return $this;
    }

    /**
     * Get shipping assignments for tests.
     *
     * @return array|null
     */
    public function getShippingAssignments()
    {
        return $this->testData['shipping_assignments'] ?? null;
    }

    /**
     * Get negotiable quote
     *
     * @return NegotiableQuoteInterface|null
     */
    public function getNegotiableQuote()
    {
        return $this->negotiableQuote;
    }

    /**
     * Set negotiable quote
     *
     * @param NegotiableQuoteInterface $negotiableQuote
     * @return $this
     */
    public function setNegotiableQuote($negotiableQuote)
    {
        $this->negotiableQuote = $negotiableQuote;
        return $this;
    }

    /**
     * Get company ID
     *
     * @return mixed
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * Set company ID
     *
     * @param mixed $companyId
     * @return $this
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
        return $this;
    }

    /**
     * Get negotiable quote item
     *
     * @return mixed
     */
    public function getNegotiableQuoteItem()
    {
        return $this->negotiableQuoteItem;
    }

    /**
     * Set negotiable quote item
     *
     * @param mixed $negotiableQuoteItem
     * @return $this
     */
    public function setNegotiableQuoteItem($negotiableQuoteItem)
    {
        $this->negotiableQuoteItem = $negotiableQuoteItem;
        return $this;
    }
}
