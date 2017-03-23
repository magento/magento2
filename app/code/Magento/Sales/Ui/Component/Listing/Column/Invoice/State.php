<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\Listing\Column\Invoice;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;

/**
 * Class State
 */
class State extends Column
{
    /**
     * @var string[]
     */
    protected $states;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        array $components = [],
        array $data = []
    ) {
        $this->states = $invoiceRepository->create()->getStates();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = isset($this->states[$item[$this->getData('name')]])
                    ? $this->states[$item[$this->getData('name')]]
                    : $item[$this->getData('name')];
            }
        }

        return $dataSource;
    }
}
