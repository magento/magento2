<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\Bulk\OperationInterface;

/**
 * Back button configuration provider
 */
class DoneButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\Bulk\BulkStatusInterface
     */
    private $bulkStatus;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Framework\Bulk\BulkStatusInterface $bulkStatus
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\Bulk\BulkStatusInterface $bulkStatus,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->bulkStatus = $bulkStatus;
        $this->request = $request;
    }

    /**
     * Retrieve button data
     *
     * @return array button configuration
     */
    public function getButtonData()
    {
        $uuid = $this->request->getParam('uuid');
        $operationsCount = $this->bulkStatus->getOperationsCountByBulkIdAndStatus(
            $uuid,
            OperationInterface::STATUS_TYPE_RETRIABLY_FAILED
        );
        $button = [];

        if ($this->request->getParam('buttons') && $operationsCount === 0) {
            $button = [
                'label' => __('Done'),
                'class' => 'primary',
                'sort_order' => 10,
                'on_click' => '',
                'data_attribute' => [
                    'mage-init' => [
                        'Magento_Ui/js/form/button-adapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'notification_area.notification_area.modalContainer.modal',
                                    'actionName' => 'closeModal'
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        return $button;
    }
}
