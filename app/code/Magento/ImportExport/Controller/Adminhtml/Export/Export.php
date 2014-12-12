<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ImportExport\Controller\Adminhtml\Export;

use Magento\Framework\App\Filesystem\DirectoryList;

class Export extends \Magento\ImportExport\Controller\Adminhtml\Export
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Load data with filter applying and create file for download.
     *
     * @return $this
     */
    public function execute()
    {
        if ($this->getRequest()->getPost(\Magento\ImportExport\Model\Export::FILTER_ELEMENT_GROUP)) {
            try {
                /** @var $model \Magento\ImportExport\Model\Export */
                $model = $this->_objectManager->create('Magento\ImportExport\Model\Export');
                $model->setData($this->getRequest()->getParams());

                return $this->_fileFactory->create(
                    $model->getFileName(),
                    $model->export(),
                    DirectoryList::VAR_DIR,
                    $model->getContentType()
                );
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                $this->messageManager->addError(__('Please correct the data sent.'));
            }
        } else {
            $this->messageManager->addError(__('Please correct the data sent.'));
        }
        return $this->_redirect('adminhtml/*/index');
    }
}
