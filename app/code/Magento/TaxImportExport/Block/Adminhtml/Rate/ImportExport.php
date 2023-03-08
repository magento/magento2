<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TaxImportExport\Block\Adminhtml\Rate;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;

/**
 * @api
 * @since 100.0.2
 */
class ImportExport extends Widget
{
    /**
     * @var string
     */
    protected $_template = 'Magento_TaxImportExport::importExport.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->setUseContainer(true);
    }
}
