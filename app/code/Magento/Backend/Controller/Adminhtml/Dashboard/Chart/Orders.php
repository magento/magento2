<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Controller\Adminhtml\Dashboard\Chart;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\Dashboard;
use Magento\Backend\Model\Dashboard\Chart;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Get order quantities chart data controller
 */
class Orders extends Dashboard implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Chart
     */
    private $chart;

    /**
     * Orders constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Chart $chart
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Chart $chart
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->chart = $chart;
    }

    /**
     * Get chart data
     *
     * @return Json
     */
    public function execute(): Json
    {
        $data = [
            'data' => $this->chart->getByPeriod(
                $this->_request->getParam('period'),
                'quantity',
                $this->_request->getParam('store'),
                $this->_request->getParam('website'),
                $this->_request->getParam('group')
            ),
            'label' => __('Quantity')
        ];

        return $this->resultJsonFactory->create()
            ->setData($data);
    }
}
