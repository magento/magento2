<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TaxImportExport\Block\Adminhtml\Rate;

/**
 * @api
 * @since 2.0.0
 */
class ImportExport extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'importExport.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->setUseContainer(true);
    }
}
