<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * Adminhtml sales order create abstract block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 2.0.0
 */
abstract class AbstractCreate extends \Magento\Backend\Block\Widget
{
    /**
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     * @since 2.0.0
     */
    protected $_sessionQuote;

    /**
     * Order create
     *
     * @var \Magento\Sales\Model\AdminOrder\Create
     * @since 2.0.0
     */
    protected $_orderCreate;

    /**
     * @var PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_sessionQuote = $sessionQuote;
        $this->_orderCreate = $orderCreate;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve create order model object
     *
     * @return \Magento\Sales\Model\AdminOrder\Create
     * @since 2.0.0
     */
    public function getCreateOrderModel()
    {
        return $this->_orderCreate;
    }

    /**
     * Retrieve quote session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     * @since 2.0.0
     */
    protected function _getSession()
    {
        return $this->_sessionQuote;
    }

    /**
     * Retrieve quote model object
     *
     * @return \Magento\Quote\Model\Quote
     * @since 2.0.0
     */
    public function getQuote()
    {
        return $this->_getSession()->getQuote();
    }

    /**
     * Retrieve customer identifier
     *
     * @return int
     * @since 2.0.0
     */
    public function getCustomerId()
    {
        return $this->_getSession()->getCustomerId();
    }

    /**
     * Retrieve store model object
     *
     * @return \Magento\Store\Model\Store
     * @since 2.0.0
     */
    public function getStore()
    {
        return $this->_getSession()->getStore();
    }

    /**
     * Retrieve store identifier
     *
     * @return int
     * @since 2.0.0
     */
    public function getStoreId()
    {
        return $this->_getSession()->getStoreId();
    }

    /**
     * Retrieve formated price
     *
     * @param float $value
     * @return string
     * @since 2.0.0
     */
    public function formatPrice($value)
    {
        return $this->priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore()
        );
    }

    /**
     * @param Product $product
     * @return string
     * @since 2.2.0
     */
    public function getItemPrice(Product $product)
    {
        $price = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
        return $this->convertPrice($price);
    }

    /**
     * Convert price
     *
     * @param int|float $value
     * @param bool $format
     * @return string|int|float
     * @since 2.0.0
     */
    public function convertPrice($value, $format = true)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat(
                $value,
                true,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $this->getStore()
            )
            : $this->priceCurrency->convert($value, $this->getStore());
    }
}
