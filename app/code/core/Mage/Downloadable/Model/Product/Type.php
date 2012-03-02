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
 * @category    Mage
 * @package     Mage_Downloadable
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Downloadable product type model
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Downloadable_Model_Product_Type extends Mage_Catalog_Model_Product_Type_Virtual
{
    const TYPE_DOWNLOADABLE = 'downloadable';

    /**
     * Get downloadable product links
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getLinks($product)
    {
        if (is_null($product->getDownloadableLinks())) {
            $_linkCollection = Mage::getModel('Mage_Downloadable_Model_Link')->getCollection()
                ->addProductToFilter($product->getId())
                ->addTitleToResult($product->getStoreId())
                ->addPriceToResult($product->getStore()->getWebsiteId());
            $linksCollectionById = array();
            foreach ($_linkCollection as $link) {
                /* @var Mage_Downloadable_Model_Link $link */

                $link->setProduct($product);
                $linksCollectionById[$link->getId()] = $link;
            }
            $product->setDownloadableLinks($linksCollectionById);
        }
        return $product->getDownloadableLinks();
    }

    /**
     * Check if product has links
     *
     * @param Mage_Catalog_Model_Product $product
     * @return boolean
     */
    public function hasLinks($product)
    {
        if ($product->hasData('links_exist')) {
            return $product->getData('links_exist');
        }
        return count($this->getLinks($product)) > 0;
    }

    /**
     * Check if product has options
     *
     * @param Mage_Catalog_Model_Product $product
     * @return boolean
     */
    public function hasOptions($product)
    {
        //return true;
        return $product->getLinksPurchasedSeparately()
            || parent::hasOptions($product);
    }

    /**
     * Check if product has required options
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function hasRequiredOptions($product)
    {
        if (parent::hasRequiredOptions($product) || $product->getLinksPurchasedSeparately()) {
            return true;
        }
        return false;
    }

    /**
     * Check if product cannot be purchased with no links selected
     *
     * @param Mage_Catalog_Model_Product $product
     * @return boolean
     */
    public function getLinkSelectionRequired($product)
    {
        return $product->getLinksPurchasedSeparately();
    }

    /**
     * Get downloadable product samples
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Downloadable_Model_Resource_Sample_Collection
     */
    public function getSamples($product)
    {
        if (is_null($product->getDownloadableSamples())) {
            $_sampleCollection = Mage::getModel('Mage_Downloadable_Model_Sample')->getCollection()
                ->addProductToFilter($product->getId())
                ->addTitleToResult($product->getStoreId());
            $product->setDownloadableSamples($_sampleCollection);
        }

        return $product->getDownloadableSamples();
    }

    /**
     * Check if product has samples
     *
     * @param Mage_Catalog_Model_Product $product
     * @return boolean
     */
    public function hasSamples($product)
    {
        return count($this->getSamples($product)) > 0;
    }

    /**
     * Save Product downloadable information (links and samples)
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Downloadable_Model_Product_Type
     */
    public function save($product)
    {
        parent::save($product);

        if ($data = $product->getDownloadableData()) {
            if (isset($data['sample'])) {
                $_deleteItems = array();
                foreach ($data['sample'] as $sampleItem) {
                    if ($sampleItem['is_delete'] == '1') {
                        if ($sampleItem['sample_id']) {
                            $_deleteItems[] = $sampleItem['sample_id'];
                        }
                    } else {
                        unset($sampleItem['is_delete']);
                        if (!$sampleItem['sample_id']) {
                            unset($sampleItem['sample_id']);
                        }
                        $sampleModel = Mage::getModel('Mage_Downloadable_Model_Sample');
                        $files = array();
                        if (isset($sampleItem['file'])) {
                            $files = Mage::helper('Mage_Core_Helper_Data')->jsonDecode($sampleItem['file']);
                            unset($sampleItem['file']);
                        }

                        $sampleModel->setData($sampleItem)
                            ->setSampleType($sampleItem['type'])
                            ->setProductId($product->getId())
                            ->setStoreId($product->getStoreId());

                        if ($sampleModel->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
                            $sampleFileName = Mage::helper('Mage_Downloadable_Helper_File')->moveFileFromTmp(
                                Mage_Downloadable_Model_Sample::getBaseTmpPath(),
                                Mage_Downloadable_Model_Sample::getBasePath(),
                                $files
                            );
                            $sampleModel->setSampleFile($sampleFileName);
                        }
                        $sampleModel->save();
                    }
                }
                if ($_deleteItems) {
                    Mage::getResourceModel('Mage_Downloadable_Model_Resource_Sample')->deleteItems($_deleteItems);
                }
            }
            if (isset($data['link'])) {
                $_deleteItems = array();
                foreach ($data['link'] as $linkItem) {
                    if ($linkItem['is_delete'] == '1') {
                        if ($linkItem['link_id']) {
                            $_deleteItems[] = $linkItem['link_id'];
                        }
                    } else {
                        unset($linkItem['is_delete']);
                        if (!$linkItem['link_id']) {
                            unset($linkItem['link_id']);
                        }
                        $files = array();
                        if (isset($linkItem['file'])) {
                            $files = Mage::helper('Mage_Core_Helper_Data')->jsonDecode($linkItem['file']);
                            unset($linkItem['file']);
                        }
                        $sample = array();
                        if (isset($linkItem['sample'])) {
                            $sample = $linkItem['sample'];
                            unset($linkItem['sample']);
                        }
                        $linkModel = Mage::getModel('Mage_Downloadable_Model_Link')
                            ->setData($linkItem)
                            ->setLinkType($linkItem['type'])
                            ->setProductId($product->getId())
                            ->setStoreId($product->getStoreId())
                            ->setWebsiteId($product->getStore()->getWebsiteId())
                            ->setProductWebsiteIds($product->getWebsiteIds());
                        if (null === $linkModel->getPrice()) {
                            $linkModel->setPrice(0);
                        }
                        if ($linkModel->getIsUnlimited()) {
                            $linkModel->setNumberOfDownloads(0);
                        }
                        $sampleFile = array();
                        if ($sample && isset($sample['type'])) {
                            if ($sample['type'] == 'url' && $sample['url'] != '') {
                                $linkModel->setSampleUrl($sample['url']);
                            }
                            $linkModel->setSampleType($sample['type']);
                            $sampleFile = Mage::helper('Mage_Core_Helper_Data')->jsonDecode($sample['file']);
                        }
                        if ($linkModel->getLinkType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
                            $linkFileName = Mage::helper('Mage_Downloadable_Helper_File')->moveFileFromTmp(
                                Mage_Downloadable_Model_Link::getBaseTmpPath(),
                                Mage_Downloadable_Model_Link::getBasePath(),
                                $files
                            );
                            $linkModel->setLinkFile($linkFileName);
                        }
                        if ($linkModel->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
                            $linkSampleFileName = Mage::helper('Mage_Downloadable_Helper_File')->moveFileFromTmp(
                                Mage_Downloadable_Model_Link::getBaseSampleTmpPath(),
                                Mage_Downloadable_Model_Link::getBaseSamplePath(),
                                $sampleFile
                            );
                            $linkModel->setSampleFile($linkSampleFileName);
                        }
                        $linkModel->save();
                    }
                }
                if ($_deleteItems) {
                    Mage::getResourceModel('Mage_Downloadable_Model_Resource_Link')->deleteItems($_deleteItems);
                }
                if ($product->getLinksPurchasedSeparately()) {
                    $product->setIsCustomOptionChanged();
                }
            }
        }

        return $this;
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then prepare options for downloadable links.
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);

        if (is_string($result)) {
            return $result;
        }
        // if adding product from admin area we add all links to product
        $originalLinksPurchasedSeparately = null;
        if ($product->getSkipCheckRequiredOption()) {
            $originalLinksPurchasedSeparately = $product->getLinksPurchasedSeparately();
            $product->setLinksPurchasedSeparately(false);
        }
        $preparedLinks = array();
        if ($product->getLinksPurchasedSeparately()) {
            if ($links = $buyRequest->getLinks()) {
                foreach ($this->getLinks($product) as $link) {
                    if (in_array($link->getId(), $links)) {
                        $preparedLinks[] = $link->getId();
                    }
                }
            }
        } else {
            foreach ($this->getLinks($product) as $link) {
                $preparedLinks[] = $link->getId();
            }
        }
        if (null !== $originalLinksPurchasedSeparately) {
            $product->setLinksPurchasedSeparately($originalLinksPurchasedSeparately);
        }
        if ($preparedLinks) {
            $product->addCustomOption('downloadable_link_ids', implode(',', $preparedLinks));
            return $result;
        }
        if ($this->getLinkSelectionRequired($product) && $this->_isStrictProcessMode($processMode)) {
            return Mage::helper('Mage_Downloadable_Helper_Data')->__('Please specify product link(s).');
        }
        return $result;
    }

    /**
     * Check if product can be bought
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Bundle_Model_Product_Type
     * @throws Mage_Core_Exception
     */
    public function checkProductBuyState($product)
    {
        parent::checkProductBuyState($product);
        $option = $product->getCustomOption('info_buyRequest');
        if ($option instanceof Mage_Sales_Model_Quote_Item_Option) {
            $buyRequest = new Varien_Object(unserialize($option->getValue()));
            if (!$buyRequest->hasLinks()) {
                if (!$product->getLinksPurchasedSeparately()) {
                    $allLinksIds = Mage::getModel('Mage_Downloadable_Model_Link')
                        ->getCollection()
                        ->addProductToFilter($product->getId())
                        ->getAllIds();
                    $buyRequest->setLinks($allLinksIds);
                    $product->addCustomOption('info_buyRequest', serialize($buyRequest->getData()));
                } else {
                    Mage::throwException(
                        Mage::helper('Mage_Downloadable_Helper_Data')->__('Please specify product link(s).')
                    );
                }
            }
        }
        return $this;
    }

    /**
     * Prepare additional options/information for order item which will be
     * created from this product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getOrderOptions($product)
    {
        $options = parent::getOrderOptions($product);
        if ($linkIds = $product->getCustomOption('downloadable_link_ids')) {
            $linkOptions = array();
            $links = $this->getLinks($product);
            foreach (explode(',', $linkIds->getValue()) as $linkId) {
                if (isset($links[$linkId])) {
                    $linkOptions[] = $linkId;
                }
            }
            $options = array_merge($options, array('links' => $linkOptions));
        }
        $options = array_merge($options, array(
            'is_downloadable' => true,
            'real_product_type' => self::TYPE_DOWNLOADABLE
        ));
        return $options;
    }



    /**
     * Setting flag if dowenloadable product can be or not in complex product
     * based on link can be purchased separately or not
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function beforeSave($product)
    {
        parent::beforeSave($product);
        if ($this->getLinkSelectionRequired($product)) {
            $product->setTypeHasRequiredOptions(true);
        } else {
            $product->setTypeHasRequiredOptions(false);
        }

        // Update links_exist attribute value
        $linksExist = false;
        if ($data = $product->getDownloadableData()) {
            if (isset($data['link'])) {
                foreach ($data['link'] as $linkItem) {
                    if (!isset($linkItem['is_delete']) || !$linkItem['is_delete']) {
                        $linksExist = true;
                        break;
                    }
                }
            }
        }

        $product->setTypeHasOptions($linksExist);
        $product->setLinksExist($linksExist);
    }

    /**
     * Retrieve additional searchable data from type instance
     * Using based on product id and store_id data
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getSearchableData($product)
    {
        $searchData = parent::getSearchableData($product);

        $linkSearchData = Mage::getSingleton('Mage_Downloadable_Model_Link')
            ->getSearchableData($product->getId(), $product->getStoreId());
        if ($linkSearchData) {
            $searchData = array_merge($searchData, $linkSearchData);
        }

        $sampleSearchData = Mage::getSingleton('Mage_Downloadable_Model_Sample')
            ->getSearchableData($product->getId(), $product->getStoreId());
        if ($sampleSearchData) {
            $searchData = array_merge($searchData, $sampleSearchData);
        }

        return $searchData;
    }

    /**
     * Check is product available for sale
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isSalable($product)
    {
        return $this->hasLinks($product) && parent::isSalable($product);
    }

    /**
     * Prepare selected options for downloadable product
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  Varien_Object $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $links = $buyRequest->getLinks();
        $links = (is_array($links)) ? array_filter($links, 'intval') : array();

        $options = array('links' => $links);

        return $options;
    }

    /**
     * Check if downloadable product has links and they can be purchased separately
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function canConfigure($product)
    {
        return $this->hasLinks($product) && $product->getLinksPurchasedSeparately();
    }
}
