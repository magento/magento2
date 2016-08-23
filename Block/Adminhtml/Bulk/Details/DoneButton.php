<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Back button configuration provider
 */
class DoneButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\AsynchronousOperations\Model\Operation\Details
     */
    private $details;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\AsynchronousOperations\Model\Operation\Details $details
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\AsynchronousOperations\Model\Operation\Details $details,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->details = $details;
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
        $details = $this->details->getDetails($uuid);
        $button = [];

        if ($this->request->getParam('buttons') && $details['failed_retriable'] === 0) {
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
