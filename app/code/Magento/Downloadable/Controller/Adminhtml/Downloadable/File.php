<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable;

/**
 * Downloadable File upload controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class File extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $_link;

    /**
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $_sample;

    /**
     * Downloadable file helper.
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $_fileHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Downloadable\Model\Link $link
     * @param \Magento\Downloadable\Model\Sample $sample
     * @param \Magento\Downloadable\Helper\File $fileHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Downloadable\Model\Link $link,
        \Magento\Downloadable\Model\Sample $sample,
        \Magento\Downloadable\Helper\File $fileHelper
    ) {
        $this->_link = $link;
        $this->_sample = $sample;
        $this->_fileHelper = $fileHelper;
        parent::__construct($context);
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::products');
    }
}
