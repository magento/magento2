<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Downloadable\Model\Resource\Link as Resource;

/**
 * Downloadable link model
 *
 * @method Resource _getResource()
 * @method Resource getResource()
 * @method int getProductId()
 * @method Link setProductId(int $value)
 * @method int getSortOrder()
 * @method Link setSortOrder(int $value)
 * @method int getNumberOfDownloads()
 * @method Link setNumberOfDownloads(int $value)
 * @method int getIsShareable()
 * @method Link setIsShareable(int $value)
 * @method string getLinkUrl()
 * @method Link setLinkUrl(string $value)
 * @method string getLinkFile()
 * @method Link setLinkFile(string $value)
 * @method string getLinkType()
 * @method Link setLinkType(string $value)
 * @method string getSampleUrl()
 * @method Link setSampleUrl(string $value)
 * @method string getSampleFile()
 * @method Link setSampleFile(string $value)
 * @method string getSampleType()
 * @method Link setSampleType(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Link extends \Magento\Framework\Model\AbstractModel
{
    const XML_PATH_LINKS_TITLE = 'catalog/downloadable/links_title';

    const XML_PATH_DEFAULT_DOWNLOADS_NUMBER = 'catalog/downloadable/downloads_number';

    const XML_PATH_TARGET_NEW_WINDOW = 'catalog/downloadable/links_target_new_window';

    const XML_PATH_CONFIG_IS_SHAREABLE = 'catalog/downloadable/shareable';

    const LINK_SHAREABLE_YES = 1;

    const LINK_SHAREABLE_NO = 0;

    const LINK_SHAREABLE_CONFIG = 2;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Downloadable\Model\Resource\Link');
        parent::_construct();
    }

    /**
     * Enter description here...
     *
     * @return $this
     */
    public function afterSave()
    {
        $this->getResource()->saveItemTitleAndPrice($this);
        return parent::afterSave();
    }

    /**
     * Retrieve base temporary path
     *
     * @return string
     */
    public function getBaseTmpPath()
    {
        return 'downloadable/tmp/links';
    }

    /**
     * Retrieve Base files path
     *
     * @return string
     */
    public function getBasePath()
    {
        return 'downloadable/files/links';
    }

    /**
     * Retrieve base sample temporary path
     *
     * @return string
     */
    public function getBaseSampleTmpPath()
    {
        return 'downloadable/tmp/link_samples';
    }

    /**
     * Retrieve base sample path
     *
     * @return string
     */
    public function getBaseSamplePath()
    {
        return 'downloadable/files/link_samples';
    }

    /**
     * Retrieve links searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        return $this->_getResource()->getSearchableData($productId, $storeId);
    }
}
