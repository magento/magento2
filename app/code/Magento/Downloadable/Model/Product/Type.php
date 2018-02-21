<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;

/**
 * Downloadable product type model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Type extends \Magento\Catalog\Model\Product\Type\Virtual
{
    const TYPE_DOWNLOADABLE = 'downloadable';

    /**
     * Downloadable file
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $_downloadableFile = null;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\SampleFactory
     */
    protected $_sampleResFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link
     */
    protected $_linkResource;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory
     */
    protected $_linksFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Sample\CollectionFactory
     */
    protected $_samplesFactory;

    /**
     * @var \Magento\Downloadable\Model\SampleFactory
     */
    protected $_sampleFactory;

    /**
     * @var \Magento\Downloadable\Model\LinkFactory
     */
    protected $_linkFactory;

    /**
     * @var TypeHandler\TypeHandlerInterface
     */
    private $typeHandler;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Downloadable\Model\ResourceModel\SampleFactory $sampleResFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link $linkResource
     * @param \Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory $linksFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Sample\CollectionFactory $samplesFactory
     * @param \Magento\Downloadable\Model\SampleFactory $sampleFactory
     * @param \Magento\Downloadable\Model\LinkFactory $linkFactory
     * @param TypeHandler\TypeHandlerInterface $typeHandler
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\Downloadable\Model\ResourceModel\SampleFactory $sampleResFactory,
        \Magento\Downloadable\Model\ResourceModel\Link $linkResource,
        \Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory $linksFactory,
        \Magento\Downloadable\Model\ResourceModel\Sample\CollectionFactory $samplesFactory,
        \Magento\Downloadable\Model\SampleFactory $sampleFactory,
        \Magento\Downloadable\Model\LinkFactory $linkFactory,
        \Magento\Downloadable\Model\Product\TypeHandler\TypeHandlerInterface $typeHandler,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->_sampleResFactory = $sampleResFactory;
        $this->_linkResource = $linkResource;
        $this->_linksFactory = $linksFactory;
        $this->_samplesFactory = $samplesFactory;
        $this->_sampleFactory = $sampleFactory;
        $this->_linkFactory = $linkFactory;
        $this->typeHandler = $typeHandler;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository
        );
    }

    /**
     * Get downloadable product links
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Downloadable\Model\Link[]
     */
    public function getLinks($product)
    {
        if ($product->getDownloadableLinks() === null) {
            /** @var \Magento\Downloadable\Model\ResourceModel\Link\Collection $linkCollection */
            $linkCollection = $this->_linksFactory->create()->addProductToFilter(
                $product->getEntityId()
            )->addTitleToResult(
                $product->getStoreId()
            )->addPriceToResult(
                $product->getStore()->getWebsiteId()
            );
            $this->extensionAttributesJoinProcessor->process($linkCollection);
            $linksCollectionById = [];
            foreach ($linkCollection as $link) {
                /* @var \Magento\Downloadable\Model\Link $link */
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
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function hasLinks($product)
    {
        $hasLinks = $product->getData('links_exist');
        if (null === $hasLinks) {
            $hasLinks = (count($this->getLinks($product)) > 0);
        }
        return $hasLinks;
    }

    /**
     * Check if product has options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function hasOptions($product)
    {
        return parent::hasOptions($product) || $this->hasLinks($product);
    }

    /**
     * Check if product has required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasRequiredOptions($product)
    {
        return (parent::hasRequiredOptions($product) || $product->getLinksPurchasedSeparately());
    }

    /**
     * Check if product cannot be purchased with no links selected
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getLinkSelectionRequired($product)
    {
        return $product->getLinksPurchasedSeparately();
    }

    /**
     * Get downloadable product samples
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Downloadable\Model\ResourceModel\Sample\Collection
     */
    public function getSamples($product)
    {
        if ($product->getDownloadableSamples() === null) {
            $sampleCollection = $this->_samplesFactory->create()->addProductToFilter(
                $product->getEntityId()
            )->addTitleToResult(
                $product->getStoreId()
            );
            $this->extensionAttributesJoinProcessor->process($sampleCollection);
            $product->setDownloadableSamples($sampleCollection);
        }

        return $product->getDownloadableSamples();
    }

    /**
     * Check if product has samples
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function hasSamples($product)
    {
        return count($this->getSamples($product)) > 0;
    }

    /**
     * Check if product can be bought
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkProductBuyState($product)
    {
        parent::checkProductBuyState($product);
        $option = $product->getCustomOption('info_buyRequest');
        if ($option instanceof \Magento\Quote\Model\Quote\Item\Option) {
            $buyRequest = new \Magento\Framework\DataObject(unserialize($option->getValue()));
            if (!$buyRequest->hasLinks()) {
                if (!$product->getLinksPurchasedSeparately()) {
                    $allLinksIds = $this->_linksFactory->create()->addProductToFilter(
                        $product->getEntityId()
                    )->getAllIds();
                    $buyRequest->setLinks($allLinksIds);
                    $product->addCustomOption('info_buyRequest', serialize($buyRequest->getData()));
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Please specify product link(s).'));
                }
            }
        }
        return $this;
    }

    /**
     * Prepare additional options/information for order item which will be
     * created from this product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getOrderOptions($product)
    {
        $options = parent::getOrderOptions($product);
        if ($linkIds = $product->getCustomOption('downloadable_link_ids')) {
            $linkOptions = [];
            $links = $this->getLinks($product);
            foreach (explode(',', $linkIds->getValue()) as $linkId) {
                if (isset($links[$linkId])) {
                    $linkOptions[] = $linkId;
                }
            }
            $options = array_merge($options, ['links' => $linkOptions]);
        }
        $options = array_merge(
            $options,
            ['is_downloadable' => true, 'real_product_type' => self::TYPE_DOWNLOADABLE]
        );
        return $options;
    }

    /**
     * Retrieve additional searchable data from type instance
     * Using based on product id and store_id data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getSearchableData($product)
    {
        $searchData = parent::getSearchableData($product);

        $linkSearchData = $this->_createLink()->getSearchableData($product->getEntityId(), $product->getStoreId());
        if ($linkSearchData) {
            $searchData = array_merge($searchData, $linkSearchData);
        }

        $sampleSearchData = $this->_createSample()->getSearchableData($product->getEntityId(), $product->getStoreId());
        if ($sampleSearchData) {
            $searchData = array_merge($searchData, $sampleSearchData);
        }

        return $searchData;
    }

    /**
     * Check is product available for sale
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isSalable($product)
    {
        return $this->hasLinks($product) && parent::isSalable($product);
    }

    /**
     * Prepare selected options for downloadable product
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  \Magento\Framework\DataObject $buyRequest
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $links = $buyRequest->getLinks();
        $links = is_array($links) ? array_filter($links, 'intval') : [];

        $options = ['links' => $links];

        return $options;
    }

    /**
     * Check if downloadable product has links and they can be purchased separately
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function canConfigure($product)
    {
        return $this->hasLinks($product) && $product->getLinksPurchasedSeparately();
    }

    /**
     * Check that product of this type has weight
     *
     * @return bool
     */
    public function hasWeight()
    {
        return false;
    }

    /**
     * Delete data specific for Downloadable product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
        if ($product->getOrigData('type_id') === \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            $downloadableData = $product->getDownloadableData();
            $sampleItems = [];
            if (isset($downloadableData['sample'])) {
                foreach ($downloadableData['sample'] as $sample) {
                    $sampleItems[] = $sample['sample_id'];
                }
            }
            if ($sampleItems) {
                $this->_sampleResFactory->create()->deleteItems($sampleItems);
            }
            $linkItems = [];
            if (isset($downloadableData['link'])) {
                foreach ($downloadableData['link'] as $link) {
                    $linkItems[] = $link['link_id'];
                }
            }
            if ($linkItems) {
                $this->_linkResource->deleteItems($linkItems);
            }
        }
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then prepare options for downloadable links.
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return \Magento\Framework\Phrase|array|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
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
        $preparedLinks = [];
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
            return __('Please specify product link(s).')->render();
        }
        return $result;
    }

    /**
     * @return \Magento\Downloadable\Model\Link
     */
    protected function _createLink()
    {
        return $this->_linkFactory->create();
    }

    /**
     * @return \Magento\Downloadable\Model\Sample
     */
    protected function _createSample()
    {
        return $this->_sampleFactory->create();
    }
}
