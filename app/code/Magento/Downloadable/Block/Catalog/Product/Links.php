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
 * @category    Magento
 * @package     Magento_Downloadable
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Block\Catalog\Product;

use Magento\Downloadable\Model\Link;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Downloadable Product Links part block
 *
 */
class Links extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $calculationModel;

    /**
     * @var \Magento\Json\EncoderInterface
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
     * @param \Magento\Tax\Model\Calculation $calculationModel
     * @param \Magento\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Customer\Service\V1\CustomerAccountServiceInterface $accountService
     * @param array $data
     * @param array $priceBlockTypes
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Tax\Model\Calculation $calculationModel,
        \Magento\Json\EncoderInterface $jsonEncoder,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Customer\Service\V1\CustomerAccountServiceInterface $accountService,
        array $data = array(),
        array $priceBlockTypes = array()
    ) {
        $this->calculationModel = $calculationModel;
        $this->jsonEncoder = $jsonEncoder;
        $this->coreData = $coreData;
        $this->accountService = $accountService;
        parent::__construct(
            $context,
            $data,
            $priceBlockTypes
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
     * @param Link $link
     * @return string
     */
    public function getFormattedLinkPrice($link)
    {
        $price = $link->getPrice();
        $store = $this->getProduct()->getStore();

        if (0 == $price) {
            return '';
        }

        if (!$this->calculationModel->getCustomerData()->getId()
            && $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)) {
            $customer = $this->accountService
                ->getCustomer($this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID));
            $this->calculationModel->setCustomerData($customer);
        }

        $taxHelper = $this->_taxData;
        $coreHelper = $this->coreData;
        $_priceInclTax = $taxHelper->getPrice($link->getProduct(), $price, true);
        $_priceExclTax = $taxHelper->getPrice($link->getProduct(), $price);

        $priceStr = '<span class="price-notice">+';
        if ($taxHelper->displayPriceIncludingTax()) {
            $priceStr .= $coreHelper->currencyByStore($_priceInclTax, $store);
        } elseif ($taxHelper->displayPriceExcludingTax()) {
            $priceStr .= $coreHelper->currencyByStore($_priceExclTax, $store);
        } elseif ($taxHelper->displayBothPrices()) {
            $priceStr .= $coreHelper->currencyByStore($_priceExclTax, $store);
            if ($_priceInclTax != $_priceExclTax) {
                $priceStr .= ' (+' . $coreHelper->currencyByStore(
                    $_priceInclTax,
                    $store
                ) . ' ' . __(
                    'Incl. Tax'
                ) . ')';
            }
        }
        $priceStr .= '</span>';

        return $priceStr;
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
        $config = array();

        foreach ($this->getLinks() as $link) {
            $config[$link->getId()] = $this->coreData->currency($link->getPrice(), false, false);
        }

        return $this->jsonEncoder->encode($config);
    }

    /**
     * @param Link $link
     * @return string
     */
    public function getLinkSamlpeUrl($link)
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
        return $this->_storeConfig->getConfig(Link::XML_PATH_LINKS_TITLE);
    }

    /**
     * Return true if target of link new window
     *
     * @return bool
     */
    public function getIsOpenInNewWindow()
    {
        return $this->_storeConfig->getConfigFlag(Link::XML_PATH_TARGET_NEW_WINDOW);
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
}
