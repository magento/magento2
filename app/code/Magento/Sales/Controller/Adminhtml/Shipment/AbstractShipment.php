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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Controller\Adminhtml\Shipment;

use Magento\App\ResponseInterface;

/**
 * Adminhtml sales orders controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class AbstractShipment extends \Magento\Backend\App\Action
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
     * Init layout, menu and breadcrumb
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Sales::sales_shipment'
        )->_addBreadcrumb(
            __('Sales'),
            __('Sales')
        )->_addBreadcrumb(
            __('Shipments'),
            __('Shipments')
        );
        return $this;
    }

    /**
     * Shipments grid
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Shipments'));

        $this->_initAction()->_addContent(
            $this->_view->getLayout()->createBlock('Magento\Sales\Block\Adminhtml\Shipment')
        );
        $this->_view->renderLayout();
    }

    /**
     * Shipment information page
     *
     * @return void
     */
    public function viewAction()
    {
        if ($shipmentId = $this->getRequest()->getParam('shipment_id')) {
            $this->_forward('view', 'order_shipment', 'admin', array('come_from' => 'shipment'));
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * @return ResponseInterface|void
     */
    public function pdfshipmentsAction()
    {
        $shipmentIds = $this->getRequest()->getPost('shipment_ids');
        if (!empty($shipmentIds)) {
            $shipments = $this->_objectManager->create(
                'Magento\Sales\Model\Resource\Order\Shipment\Collection'
            )->addAttributeToSelect(
                '*'
            )->addAttributeToFilter(
                'entity_id',
                array('in' => $shipmentIds)
            )->load();
            if (!isset($pdf)) {
                $pdf = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Shipment')->getPdf($shipments);
            } else {
                $pages = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Shipment')->getPdf($shipments);
                $pdf->pages = array_merge($pdf->pages, $pages->pages);
            }
            $date = $this->_objectManager->get('Magento\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
            return $this->_fileFactory->create(
                'packingslip' . $date . '.pdf',
                $pdf->render(),
                \Magento\App\Filesystem::VAR_DIR,
                'application/pdf'
            );
        }
        $this->_redirect('sales/*/');
    }

    /**
     * @return ResponseInterface|void
     */
    public function printAction()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        if ($shipmentId) {
            $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
            if ($shipment) {
                $pdf = $this->_objectManager->create(
                    'Magento\Sales\Model\Order\Pdf\Shipment'
                )->getPdf(
                    array($shipment)
                );
                $date = $this->_objectManager->get('Magento\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                return $this->_fileFactory->create(
                    'packingslip' . $date . '.pdf',
                    $pdf->render(),
                    \Magento\App\Filesystem::VAR_DIR,
                    'application/pdf'
                );
            }
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::shipment');
    }
}
