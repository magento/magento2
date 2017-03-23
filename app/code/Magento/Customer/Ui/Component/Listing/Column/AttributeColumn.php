<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Customer\Api\Data\AttributeMetadataInterface as AttributeMetadata;

class AttributeColumn extends Column
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param AttributeRepository $attributeRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        AttributeRepository $attributeRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $metaData = $this->attributeRepository->getMetadataByCode($this->getName());
        if ($metaData && count($metaData[AttributeMetadata::OPTIONS])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (!isset($item[$this->getName()])) {
                    continue;
                }
                foreach ($metaData[AttributeMetadata::OPTIONS] as $option) {
                    if ($option['value'] == $item[$this->getName()]) {
                        $item[$this->getName()] = $option['label'];
                        break;
                    }
                }
            }
        }

        return $dataSource;
    }
}
