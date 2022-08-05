<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Block\Adminhtml\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency as BackendCurrency;
use Magento\Backend\Block\Context;
use Magento\Directory\Model\Currency\DefaultLocator;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Currency_Exception;

/**
 * Adminhtml grid item renderer currency
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 100.0.2
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Currency extends BackendCurrency
{
    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DefaultLocator $currencyLocator
     * @param CurrencyFactory $currencyFactory
     * @param CurrencyInterface $localeCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DefaultLocator $currencyLocator,
        CurrencyFactory $currencyFactory,
        CurrencyInterface $localeCurrency,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $storeManager,
            $currencyLocator,
            $currencyFactory,
            $localeCurrency,
            $data
        );
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * Renders grid column
     *
     * @param DataObject $row
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Zend_Currency_Exception
     */
    public function render(DataObject $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());
        $currencyCode = $this->getStoreCurrencyCode($row);

        if (!$currencyCode) {
            return $data;
        }

        $rate = $this->getStoreCurrencyRate($currencyCode, $row);

        $data = (float)$data * $rate;
        $data = sprintf("%f", $data);
        $data = $this->_localeCurrency->getCurrency($currencyCode)->toCurrency($data);
        return $data;
    }

    /**
     * Get admin currency code
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getAdminCurrencyCode(): string
    {
        $adminWebsiteId = (int) $this->_storeManager
            ->getStore(Store::ADMIN_CODE)
            ->getWebsiteId();
        return (string) $this->_storeManager
            ->getWebsite($adminWebsiteId)
            ->getBaseCurrencyCode();
    }

    /**
     * Get store currency code
     *
     * @param DataObject $row
     * @return string
     * @throws NoSuchEntityException
     */
    private function getStoreCurrencyCode(DataObject $row): string
    {
        $catalogPriceScope = $this->getCatalogPriceScope();
        $storeId = $this->_request->getParam('store_ids');
        if ($catalogPriceScope != 0 && !empty($storeId)) {
            $currencyCode = $this->_storeManager->getStore($storeId)->getBaseCurrencyCode();
        } elseif ($catalogPriceScope != 0) {
            $currencyCode = $this->_currencyLocator->getDefaultCurrency($this->_request);
        } else {
            $currencyCode = $this->_getCurrencyCode($row);
        }
        return $currencyCode;
    }

    /**
     * Get store currency rate
     *
     * @param string $currencyCode
     * @param DataObject $row
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getStoreCurrencyRate(string $currencyCode, DataObject $row): float
    {
        $catalogPriceScope = $this->getCatalogPriceScope();
        $adminCurrencyCode = $this->getAdminCurrencyCode();

        if (($catalogPriceScope != 0
            && $adminCurrencyCode !== $currencyCode)) {
            $storeCurrency = $this->currencyFactory->create()->load($adminCurrencyCode);
            $currencyRate = $storeCurrency->getRate($currencyCode);
        } else {
            $currencyRate = $this->_getRate($row);
        }
        return (float) $currencyRate;
    }

    /**
     * Get catalog price scope from the admin config
     *
     * @return int
     */
    private function getCatalogPriceScope(): int
    {
        return (int) $this->_scopeConfig->getValue(
            Store::XML_PATH_PRICE_SCOPE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
