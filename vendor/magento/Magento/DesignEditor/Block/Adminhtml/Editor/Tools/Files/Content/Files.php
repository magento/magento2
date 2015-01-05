<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Files\Content;

/**
 * Files files block
 *
 * @method \Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Content\Files
 *    setStorage(\Magento\Theme\Model\Wysiwyg\Storage $storage)
 * @method \Magento\Theme\Model\Wysiwyg\Storage getStorage
 */
class Files extends \Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Content\Files
{
    /**
     * @var \Magento\Theme\Helper\Storage
     */
    protected $_storageHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Theme\Helper\Storage $storageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Theme\Helper\Storage $storageHelper,
        array $data = []
    ) {
        $this->_storageHelper = $storageHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getStorageType()
    {
        return __($this->_storageHelper->getStorageType());
    }
}
