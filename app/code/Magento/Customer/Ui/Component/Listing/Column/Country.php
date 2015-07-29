<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Directory\Model\CountryFactory;

class Country extends Column
{
    /** @var \Magento\Directory\Model\CountryFactory */
    protected $countryFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CountryFactory $countryFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CountryFactory $countryFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->countryFactory = $countryFactory;
    }

    /**
     * Get Country name
     *
     * @param array $dataSource
     */
    public function prepareDataSource(array & $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item[$this->getData('name')]) {
                    $item[$this->getData('name')] = $this->countryFactory->create()->load($item[$this->getData('name')])
                        ->getName();
                }
            }
        }
    }
}
