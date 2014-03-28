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
 * @package     Magento_Rss
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Rss\Block\Catalog;

/**
 * Review form block
 */
class Special extends \Magento\Rss\Block\Catalog\AbstractCatalog
{
    /**
     * \Magento\Stdlib\DateTime\DateInterface object for date comparsions
     *
     * @var \Magento\Stdlib\DateTime\Date
     */
    protected static $_currentDate = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Rss\Model\RssFactory
     */
    protected $_rssFactory;

    /**
     * @var \Magento\Model\Resource\Iterator
     */
    protected $_resourceIterator;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $_outputHelper;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\App\Http\Context $httpContext
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Rss\Model\RssFactory $rssFactory
     * @param \Magento\Model\Resource\Iterator $resourceIterator
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Helper\Output $outputHelper
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Rss\Model\RssFactory $rssFactory,
        \Magento\Model\Resource\Iterator $resourceIterator,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Helper\Output $outputHelper,
        array $data = array()
    ) {
        $this->_outputHelper = $outputHelper;
        $this->_imageHelper = $imageHelper;
        $this->_coreData = $coreData;
        $this->_productFactory = $productFactory;
        $this->_rssFactory = $rssFactory;
        $this->_resourceIterator = $resourceIterator;
        parent::__construct($context, $httpContext, $catalogData, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        /*
         * setting cache to save the rss for 10 minutes
         */
        $this->setCacheKey('rss_catalog_special_' . $this->_getStoreId() . '_' . $this->_getCustomerGroupId());
        $this->setCacheLifetime(600);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        //store id is store view id
        $storeId = $this->_getStoreId();
        $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();

        //customer group id
        $customerGroupId = $this->_getCustomerGroupId();

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->_productFactory->create();
        $product->setStoreId($storeId);
        $specials = $product->getResourceCollection()->addPriceDataFieldFilter(
            '%s < %s',
            array('final_price', 'price')
        )->addPriceData(
            $customerGroupId,
            $websiteId
        )->addAttributeToSelect(
            array(
                'name',
                'short_description',
                'description',
                'price',
                'thumbnail',
                'special_price',
                'special_to_date',
                'msrp_enabled',
                'msrp_display_actual_price_type',
                'msrp'
            ),
            'left'
        )->addAttributeToSort(
            'name',
            'asc'
        );

        $newUrl = $this->_urlBuilder->getUrl('rss/catalog/special/store_id/' . $storeId);
        $title = __('%1 - Special Products', $this->_storeManager->getStore()->getFrontendName());
        $lang = $this->_storeConfig->getConfig('general/locale/code');
        /** @var $rssObj \Magento\Rss\Model\Rss */
        $rssObj = $this->_rssFactory->create();
        $rssObj->_addHeader(
            array(
                'title' => $title,
                'description' => $title,
                'link' => $newUrl,
                'charset' => 'UTF-8',
                'language' => $lang
            )
        );

        $results = array();
        /*
        using resource iterator to load the data one by one
        instead of loading all at the same time. loading all data at the same time can cause the big memory allocation.
        */
        $this->_resourceIterator->walk(
            $specials->getSelect(),
            array(array($this, 'addSpecialXmlCallback')),
            array('rssObj' => $rssObj, 'results' => &$results)
        );

        if (sizeof($results) > 0) {
            foreach ($results as $result) {
                // render a row for RSS feed
                $product->setData($result);
                $html = sprintf(
                    '<table><tr>
                    <td><a href="%s"><img src="%s" alt="" border="0" align="left" height="75" width="75" /></a></td>
                    <td style="text-decoration:none;">%s',
                    $product->getProductUrl(),
                    $this->_imageHelper->init($product, 'thumbnail')->resize(75, 75),
                    $this->_outputHelper->productAttribute($product, $product->getDescription(), 'description')
                );

                // add price data if needed
                if ($product->getAllowedPriceInRss()) {
                    if ($this->_catalogData->canApplyMsrp($product)) {
                        $html .= '<br/><a href="' . $product->getProductUrl() . '">' . __('Click for price') . '</a>';
                    } else {
                        $special = '';
                        if ($result['use_special']) {
                            $special = '<br />' . __(
                                'Special Expires On: %1',
                                $this->formatDate(
                                    $result['special_to_date'],
                                    \Magento\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM
                                )
                            );
                        }
                        $html .= sprintf(
                            '<p>%s %s%s</p>',
                            __('Price: %1', $this->_coreData->currency($result['price'])),
                            __('Special Price: %1', $this->_coreData->currency($result['final_price'])),
                            $special
                        );
                    }
                }

                $html .= '</td></tr></table>';

                $rssObj->_addEntry(
                    array('title' => $product->getName(), 'link' => $product->getProductUrl(), 'description' => $html)
                );
            }
        }
        return $rssObj->createRssXml();
    }

    /**
     * Preparing data and adding to rss object
     *
     * @param array $args
     * @return void
     */
    public function addSpecialXmlCallback($args)
    {
        if (!isset(self::$_currentDate)) {
            self::$_currentDate = new \Magento\Stdlib\DateTime\Date();
        }

        // dispatch event to determine whether the product will eventually get to the result
        $product = new \Magento\Object(array('allowed_in_rss' => true, 'allowed_price_in_rss' => true));
        $args['product'] = $product;
        $this->_eventManager->dispatch('rss_catalog_special_xml_callback', $args);
        if (!$product->getAllowedInRss()) {
            return;
        }

        // add row to result and determine whether special price is active (less or equal to the final price)
        $row = $args['row'];
        $row['use_special'] = false;
        $row['allowed_price_in_rss'] = $product->getAllowedPriceInRss();
        if (isset(
            $row['special_to_date']
        ) && $row['final_price'] <= $row['special_price'] && $row['allowed_price_in_rss']
        ) {
            $compareDate = self::$_currentDate->compareDate(
                $row['special_to_date'],
                \Magento\Stdlib\DateTime::DATE_INTERNAL_FORMAT
            );
            if (-1 === $compareDate || 0 === $compareDate) {
                $row['use_special'] = true;
            }
        }

        $args['results'][] = $row;
    }

    /**
     * Function for comparing two items in collection
     *
     * @param array $a
     * @param array $b
     * @return bool
     */
    public function sortByStartDate($a, $b)
    {
        return $a['start_date'] > $b['start_date'] ? -1 : ($a['start_date'] < $b['start_date'] ? 1 : 0);
    }
}
