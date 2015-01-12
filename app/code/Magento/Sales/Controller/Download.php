<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Download as ModelDownload;

/**
 * Sales controller for download purposes
 */
class Download extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Model\Download
     */
    protected $_download;

    /**
     * @param Context $context
     * @param ModelDownload $download
     */
    public function __construct(Context $context, ModelDownload $download)
    {
        $this->_download = $download;
        parent::__construct($context);
    }
}
