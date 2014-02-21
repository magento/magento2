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
 * @category    Magento
 * @package     Magento_ImportExport
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Export controller
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ImportExport\Controller\Adminhtml;

class Export extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Initialize layout.
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_title->add(__('Import/Export'));
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_ImportExport::system_convert_export');

        return $this;
    }

    /**
     * Check access (in the ACL) for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_ImportExport::export');
    }

    /**
     * Load data with filter applying and create file for download.
     *
     * @return $this
     */
    public function exportAction()
    {
        if ($this->getRequest()->getPost(\Magento\ImportExport\Model\Export::FILTER_ELEMENT_GROUP)) {
            try {
                /** @var $model \Magento\ImportExport\Model\Export */
                $model = $this->_objectManager->create('Magento\ImportExport\Model\Export');
                $model->setData($this->getRequest()->getParams());

                return $this->_fileFactory->create(
                    $model->getFileName(),
                    $model->export(),
                    \Magento\App\Filesystem::VAR_DIR,
                    $model->getContentType()
                );
            } catch (\Magento\Core\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Logger')->logException($e);
                $this->messageManager->addError(__('Please correct the data sent.'));
            }
        } else {
            $this->messageManager->addError(__('Please correct the data sent.'));
        }
        return $this->_redirect('adminhtml/*/index');
    }

    /**
     * Index action.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title->add(__('Export'));
        $this->_addBreadcrumb(__('Export'), __('Export'));

        $this->_view->renderLayout();
    }

    /**
     * Get grid-filter of entity attributes action.
     *
     * @return void
     */
    public function getFilterAction()
    {
        $data = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest() && $data) {
            try {
                $this->_view->loadLayout();

                /** @var $attrFilterBlock \Magento\ImportExport\Block\Adminhtml\Export\Filter */
                $attrFilterBlock = $this->_view->getLayout()->getBlock('export.filter');
                /** @var $export \Magento\ImportExport\Model\Export */
                $export = $this->_objectManager->create('Magento\ImportExport\Model\Export');
                $export->setData($data);

                $export->filterAttributeCollection(
                    $attrFilterBlock->prepareCollection(
                        $export->getEntityAttributeCollection()
                    )
                );
                $this->_view->renderLayout();
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Please correct the data sent.'));
        }
        $this->_redirect('adminhtml/*/index');
    }
}
