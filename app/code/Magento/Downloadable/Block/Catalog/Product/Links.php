<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Block\Catalog\Product;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Pricing\Price\LinkPrice;

/**
 * Downloadable Product Links part block
 *
 */
class Links extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $coreData;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface
     */
    protected $accountService;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Customer\Service\V1\CustomerAccountServiceInterface $accountService
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Customer\Service\V1\CustomerAccountServiceInterface $accountService,
        array $data = array()
    ) {
        $this->coreData = $coreData;
        $this->accountService = $accountService;
        parent::__construct(
            $context,
            $data
        );
        $this->_isScopePrivate = true;
    }

    /**
     * Enter description here...
     *
     * @return boolean
     */
    public function getLinksPurchasedSeparately()
    {
        return $this->getProduct()->getLinksPurchasedSeparately();
    }

    /**
     * @return boolean
     */
    public function getLinkSelectionRequired()
    {
        return $this->getProduct()->getTypeInstance()->getLinkSelectionRequired($this->getProduct());
    }

    /**
     * @return boolean
     */
    public function hasLinks()
    {
        return $this->getProduct()->getTypeInstance()->hasLinks($this->getProduct());
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return $this->getProduct()->getTypeInstance()->getLinks($this->getProduct());
    }

    /**
     * Returns price converted to current currency rate
     *
     * @param float $price
     * @return float
     */
    public function getCurrencyPrice($price)
    {
        $store = $this->getProduct()->getStore();
        return $this->coreData->currencyByStore($price, $store, false);
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        $priceInfo = $this->getProduct()->getPriceInfo();
        $finalPrice = $priceInfo->getPrice(FinalPrice::PRICE_CODE);
        $regularPrice = $priceInfo->getPrice(RegularPrice::PRICE_CODE);
        $config = [
            'price' => $this->coreData->currency(
                $finalPrice->getAmount()->getValue(),
                false,
                false
            ),
            'oldPrice' => $this->coreData->currency(
                $regularPrice->getValue(),
                false,
                false
            )
        ];
        $config['links'] = $this->getLinksConfig();

        return json_encode($config);
    }

    /**
     * Get links price config
     *
     * @return array
     */
    protected function getLinksConfig()
    {
        $finalPrice = $this->getProduct()->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE);
        $linksConfig = [];
        foreach ($this->getLinks() as $link) {
            $amount = $finalPrice->getCustomAmount($link->getPrice());
            $price = $this->coreData->currency($link->getPrice(), false, false);
            $linksConfig[$link->getId()] = [
                'price' => $price,
                'oldPrice' => $price,
                'inclTaxPrice' => $this->coreData->currency(
                    $amount->getValue(),
                    false,
                    false
                ),
                'exclTaxPrice' => $this->coreData->currency(
                    $amount->getBaseAmount(),
                    false,
                    false
                )
            ];
        }
        return $linksConfig;
    }

    /**
     * @param Link $link
     * @return string
     */
    public function getLinkSampleUrl($link)
    {
        $store = $this->getProduct()->getStore();
        return $store->getUrl('downloadable/download/linkSample', array('link_id' => $link->getId()));
    }

    /**
     * Return title of links section
     *
     * @return string
     */
    public function getLinksTitle()
    {
        if ($this->getProduct()->getLinksTitle()) {
            return $this->getProduct()->getLinksTitle();
        }
        return $this->_scopeConfig->getValue(\Magento\Downloadable\Model\Link::XML_PATH_LINKS_TITLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Return true if target of link new window
     *
     * @return bool
     */
    public function getIsOpenInNewWindow()
    {
        return $this->_scopeConfig->isSetFlag(\Magento\Downloadable\Model\Link::XML_PATH_TARGET_NEW_WINDOW, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Returns whether link checked by default or not
     *
     * @param Link $link
     * @return bool
     */
    public function getIsLinkChecked($link)
    {
        $configValue = $this->getProduct()->getPreconfiguredValues()->getLinks();
        if (!$configValue || !is_array($configValue)) {
            return false;
        }

        return $configValue && in_array($link->getId(), $configValue);
    }

    /**
     * Returns value for link's input checkbox - either 'checked' or ''
     *
     * @param Link $link
     * @return string
     */
    public function getLinkCheckedValue($link)
    {
        return $this->getIsLinkChecked($link) ? 'checked' : '';
    }

    /**
     * @param Link $link
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected function getLinkAmount($link)
    {
        return $this->getPriceType()->getLinkAmount($link);
    }

    /**
     * @param Link $link
     * @return string
     */
    public function getLinkPrice(Link $link)
    {
        return $this->getLayout()->getBlock('product.price.render.default')->renderAmount(
            $this->getLinkAmount($link),
            $this->getPriceType(),
            $this->getProduct()
        );
    }

    /**
     * Get LinkPrice Type
     *
     * @return \Magento\Framework\Pricing\Price\PriceInterface
     */
    protected function getPriceType()
    {
        return $this->getProduct()->getPriceInfo()->getPrice(LinkPrice::PRICE_CODE);
    }
}
