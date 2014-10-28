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
        array $data = array()
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
