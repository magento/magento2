<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TaxImportExport\Controller\Adminhtml;

/**
 * Adminhtml tax rate controller
 */
class Rate extends \Magento\Backend\App\Action
{
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
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'importExport':
                return $this->_authorization->isAllowed('Magento_TaxImportExport::import_export');
            case 'importPost':
            case 'exportPost':
                return $this->_authorization->isAllowed(
                    'Magento_Tax::manage_tax'
                ) || $this->_authorization->isAllowed(
                    'Magento_TaxImportExport::import_export'
                );
            default:
                return $this->_authorization->isAllowed('Magento_Tax::manage_tax');
        }
    }
}
