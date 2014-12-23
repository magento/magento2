<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \Magento\Backup\Controller\Adminhtml\Index
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Backup\Factory $backupFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Backup\Model\BackupFactory $backupModelFactory
     * @param \Magento\Framework\App\MaintenanceMode $maintenanceMode
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Backup\Factory $backupFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Backup\Model\BackupFactory $backupModelFactory,
        \Magento\Framework\App\MaintenanceMode $maintenanceMode,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    )
    {
        parent::__construct(
            $context,
            $coreRegistry,
            $backupFactory,
            $fileFactory,
            $backupModelFactory,
            $maintenanceMode
        );
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * Download backup action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        /* @var $backup \Magento\Backup\Model\Backup */
        $backup = $this->_backupModelFactory->create(
            $this->getRequest()->getParam('time'),
            $this->getRequest()->getParam('type')
        );

        if (!$backup->getTime() || !$backup->exists()) {
            return $this->_redirect('backup/*');
        }

        $fileName = $this->_objectManager->get('Magento\Backup\Helper\Data')->generateBackupDownloadName($backup);

        $this->_response = $this->_fileFactory->create(
            $fileName,
            null,
            DirectoryList::VAR_DIR,
            'application/octet-stream',
            $backup->getSize()
        );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($backup->output());
        return $resultRaw;
    }
}
