<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Payment\Helper\Data;

/**
 * Class PaymentMethod
 * @since 2.0.0
 */
class PaymentMethod extends Column
{
    /**
     * @var Data
     * @since 2.0.0
     */
    protected $paymentHelper;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Data $paymentHelper
     * @param array $components
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Data $paymentHelper,
        array $components = [],
        array $data = []
    ) {
        $this->paymentHelper = $paymentHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @since 2.0.0
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                try {
                    $item[$this->getData('name')] = $this->paymentHelper
                        ->getMethodInstance($item[$this->getData('name')])
                        ->getTitle();
                } catch (\Exception $exception) {
                    //Displaying payment code (with no changes) if payment method is not available in system
                }
            }
        }

        return $dataSource;
    }
}
