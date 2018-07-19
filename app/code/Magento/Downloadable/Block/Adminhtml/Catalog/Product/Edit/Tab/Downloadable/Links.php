<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

/**
 * Adminhtml catalog product downloadable items tab links section
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Links extends \Magento\Backend\Block\Template
{
    /**
     * Block config data
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_config;

    /**
     * Purchased Separately Attribute cache
     *
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_purchasedSeparatelyAttribute = null;

    /**
     * @var string
     */
    protected $_template = 'Magento_Downloadable::product/edit/downloadable/links.phtml';

    /**
     * Downloadable file
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $_downloadableFile = null;

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDb = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $_link;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_sourceModel;

    /**
     * @var \Magento\Backend\Model\UrlFactory
     */
    protected $_urlFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param \Magento\Downloadable\Helper\File $downloadableFile
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Config\Model\Config\Source\Yesno $sourceModel
     * @param \Magento\Downloadable\Model\Link $link
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Backend\Model\UrlFactory $urlFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Downloadable\Helper\File $downloadableFile,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Config\Model\Config\Source\Yesno $sourceModel,
        \Magento\Downloadable\Model\Link $link,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Backend\Model\UrlFactory $urlFactory,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $coreRegistry;
        $this->_coreFileStorageDb = $coreFileStorageDatabase;
        $this->_downloadableFile = $downloadableFile;
        $this->_sourceModel = $sourceModel;
        $this->_link = $link;
        $this->_attributeFactory = $attributeFactory;
        $this->_urlFactory = $urlFactory;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }

    /**
     * Get product that is being edited
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Retrieve Purchased Separately Attribute object
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getPurchasedSeparatelyAttribute()
    {
        if ($this->_purchasedSeparatelyAttribute === null) {
            $_attributeCode = 'links_purchased_separately';

            $this->_purchasedSeparatelyAttribute = $this->_attributeFactory->create()->loadByCode(
                \Magento\Catalog\Model\Product::ENTITY,
                $_attributeCode
            );
        }

        return $this->_purchasedSeparatelyAttribute;
    }

    /**
     * Get Links can be purchased separately value for current product
     *
     * @return bool
     */
    public function isProductLinksCanBePurchasedSeparately()
    {
        return (bool) $this->getProduct()->getData('links_purchased_separately');
    }

    /**
     * Retrieve Add button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        $addButton = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => __('Add New Link'),
                'id' => 'add_link_item',
                'class' => 'action-add',
                'data_attribute' => ['action' => 'add-link'],
            ]
        );
        return $addButton->toHtml();
    }

    /**
     * Retrieve default links title
     *
     * @return string
     */
    public function getLinksTitle()
    {
        return $this->getProduct()->getId() &&
            $this->getProduct()->getTypeId() ==
            'downloadable' ? $this->getProduct()->getLinksTitle() : $this->_scopeConfig->getValue(
                \Magento\Downloadable\Model\Link::XML_PATH_LINKS_TITLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * Check exists defined links title
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUsedDefault()
    {
        return $this->getProduct()->getAttributeDefaultValue('links_title') === false;
    }

    /**
     * Return true if price in website scope
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsPriceWebsiteScope()
    {
        $scope = (int)$this->_scopeConfig->getValue(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE) {
            return true;
        }
        return false;
    }

    /**
     * Return array of links
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getLinkData()
    {
        $linkArr = [];
        if ($this->getProduct()->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $linkArr;
        }
        $links = $this->getProduct()->getTypeInstance()->getLinks($this->getProduct());
        $priceWebsiteScope = $this->getIsPriceWebsiteScope();
        $fileHelper = $this->_downloadableFile;
        foreach ($links as $item) {
            $tmpLinkItem = [
                'link_id' => $item->getId(),
                'title' => $this->escapeHtml($item->getTitle()),
                'price' => $this->getCanReadPrice() ? $this->getPriceValue($item->getPrice()) : '',
                'number_of_downloads' => $item->getNumberOfDownloads(),
                'is_shareable' => $item->getIsShareable(),
                'link_url' => $item->getLinkUrl(),
                'link_type' => $item->getLinkType(),
                'sample_file' => $item->getSampleFile(),
                'sample_url' => $item->getSampleUrl(),
                'sample_type' => $item->getSampleType(),
                'sort_order' => $item->getSortOrder(),
            ];

            $linkFile = $item->getLinkFile();
            if ($linkFile) {
                $file = $fileHelper->getFilePath($this->_link->getBasePath(), $linkFile);

                $fileExist = $fileHelper->ensureFileInFilesystem($file);

                if ($fileExist) {
                    $name = '<a href="' . $this->getUrl(
                        'adminhtml/downloadable_product_edit/link',
                        ['id' => $item->getId(), 'type' => 'link', '_secure' => true]
                    ) . '">' . $fileHelper->getFileFromPathFile(
                        $linkFile
                    ) . '</a>';
                    $tmpLinkItem['file_save'] = [
                        [
                            'file' => $linkFile,
                            'name' => $name,
                            'size' => $fileHelper->getFileSize($file),
                            'status' => 'old',
                        ],
                    ];
                }
            }

            $sampleFile = $item->getSampleFile();
            if ($sampleFile) {
                $file = $fileHelper->getFilePath($this->_link->getBaseSamplePath(), $sampleFile);

                $fileExist = $fileHelper->ensureFileInFilesystem($file);

                if ($fileExist) {
                    $name = '<a href="' . $this->getUrl(
                        'adminhtml/downloadable_product_edit/link',
                        ['id' => $item->getId(), 'type' => 'sample', '_secure' => true]
                    ) . '">' . $fileHelper->getFileFromPathFile(
                        $sampleFile
                    ) . '</a>';
                    $tmpLinkItem['sample_file_save'] = [
                        [
                            'file' => $item->getSampleFile(),
                            'name' => $name,
                            'size' => $fileHelper->getFileSize($file),
                            'status' => 'old',
                        ],
                    ];
                }
            }

            if ($item->getNumberOfDownloads() == '0') {
                $tmpLinkItem['is_unlimited'] = ' checked="checked"';
            }
            if ($this->getProduct()->getStoreId() && $item->getStoreTitle()) {
                $tmpLinkItem['store_title'] = $item->getStoreTitle();
            }
            if ($this->getProduct()->getStoreId() && $priceWebsiteScope) {
                $tmpLinkItem['website_price'] = $item->getWebsitePrice();
            }
            $linkArr[] = new \Magento\Framework\DataObject($tmpLinkItem);
        }
        return $linkArr;
    }

    /**
     * Return formatted price with two digits after decimal point
     *
     * @param float $value
     * @return string
     */
    public function getPriceValue($value)
    {
        return number_format($value, 2, null, '');
    }

    /**
     * Retrieve max downloads value from config
     *
     * @return int
     */
    public function getConfigMaxDownloads()
    {
        return $this->_scopeConfig->getValue(
            \Magento\Downloadable\Model\Link::XML_PATH_DEFAULT_DOWNLOADS_NUMBER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Prepare block Layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'upload_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'id' => '',
                'label' => __('Upload Files'),
                'type' => 'button',
                'onclick' => 'Downloadable.massUploadByType(\'links\');Downloadable.massUploadByType(\'linkssample\')'
            ]
        );
    }

    /**
     * Retrieve Upload button HTML
     *
     * @return string
     */
    public function getUploadButtonHtml()
    {
        return $this->getChildBlock('upload_button')->toHtml();
    }

    /**
     * Retrieve File Field Name
     *
     * @param string $type
     * @return string
     */
    public function getFileFieldName($type)
    {
        return $type;
    }

    /**
     * Retrieve Upload URL
     *
     * @param string $type
     * @return string
     */
    public function getUploadUrl($type)
    {
        return $this->_urlFactory->create()->addSessionParam()->getUrl(
            'adminhtml/downloadable_file/upload',
            ['type' => $type, '_secure' => true]
        );
    }

    /**
     * Retrieve config json
     *
     * @param string $type
     * @return string
     */
    public function getConfigJson($type = 'links')
    {
        $this->getConfig()->setUrl($this->getUploadUrl($type));
        $this->getConfig()->setParams(['form_key' => $this->getFormKey()]);
        $this->getConfig()->setFileField($this->getFileFieldName($type));
        $this->getConfig()->setFilters(['all' => ['label' => __('All Files'), 'files' => ['*.*']]]);
        $this->getConfig()->setReplaceBrowseWithRemove(true);
        $this->getConfig()->setWidth('32');
        $this->getConfig()->setHideUploadButton(true);
        return $this->_jsonEncoder->encode($this->getConfig()->getData());
    }

    /**
     * Retrieve config object
     *
     * @return \Magento\Framework\DataObject
     */
    public function getConfig()
    {
        if ($this->_config === null) {
            $this->_config = new \Magento\Framework\DataObject();
        }

        return $this->_config;
    }

    /**
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * @param null|string|bool|int|\Magento\Store\Model\Store $storeId $storeId
     * @return string
     */
    public function getBaseCurrencyCode($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getBaseCurrencyCode();
    }

    /**
     * @param null|string|bool|int|\Magento\Store\Model\Store $storeId $storeId
     * @return string
     */
    public function getBaseCurrencySymbol($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getBaseCurrency()->getCurrencySymbol();
    }
}
