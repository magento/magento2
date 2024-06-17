<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TaxImportExport\Controller\Adminhtml;

/**
 * Adminhtml tax rate controller
 */
abstract class Rate extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Tax::manage_tax';

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Check ACL permission
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return match (strtolower($this->getRequest()->getActionName())) {
            'importexport','importpost','exportcsv','exportxml', 'exportpost' =>
            $this->_authorization->isAllowed('Magento_TaxImportExport::import_export'),
            default =>
            $this->_authorization->isAllowed(self::ADMIN_RESOURCE),
        };
    }
}
