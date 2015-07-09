<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\Listing\Column\Creditmemo;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Model\Order\CreditmemoFactory;

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
     * @param CreditmemoFactory $creditmemoFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CreditmemoFactory $creditmemoFactory,
        array $components = [],
        array $data = []
    ) {
        $this->states = $creditmemoFactory->create()->getStates();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array & $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->states[$item[$this->getData('name')]];
            }
        }
    }
}
