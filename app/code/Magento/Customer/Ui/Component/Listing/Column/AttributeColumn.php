<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Api\MetadataInterface;

class AttributeColumn extends Column
{
    /** @var MetadataInterface */
    protected $metadata;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param MetadataInterface $metadata
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        MetadataInterface $metadata,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->metadata = $metadata;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array &$dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return;
        }
        $options = $this->metadata->getAttributeMetadata($this->getName())->getOptions();
        if (count($options)) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (!isset($item[$this->getName()])) {
                    continue;
                }
                foreach ($options as $option) {
                    if ($option->getValue() == $item[$this->getName()]) {
                        $item[$this->getName()] = $option->getLabel();
                        break;
                    }
                }
            }
        }
    }
}
