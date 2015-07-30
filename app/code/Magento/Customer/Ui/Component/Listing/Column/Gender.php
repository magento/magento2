<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Api\CustomerMetadataInterface;

class Gender extends Column
{
    /** @var CustomerMetadataInterface */
    protected $customerMetadata;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CustomerMetadataInterface $customerMetadata
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerMetadataInterface $customerMetadata,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->customerMetadata = $customerMetadata;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array & $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return;
        }
        $genderOptions = $this->customerMetadata->getAttributeMetadata('gender')->getOptions();
        foreach ($dataSource['data']['items'] as & $item) {
            if (!isset($item[$this->getData('name')])) {
                continue;
            }
            foreach ($genderOptions as $option) {
                if ($option->getValue() == $item[$this->getData('name')]) {
                    $item[$this->getData('name')] = $option->getLabel();
                    break;
                }
            }
        }
    }
}
