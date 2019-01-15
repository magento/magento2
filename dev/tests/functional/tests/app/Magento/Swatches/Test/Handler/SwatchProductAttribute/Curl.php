<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Handler\SwatchProductAttribute;

use Magento\Catalog\Test\Handler\CatalogProductAttribute\Curl as CatalogProductAttributeCurl;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\System\Event\EventManagerInterface;

/**
 * Curl handler for creating Swatch Attribute.
 */
class Curl extends CatalogProductAttributeCurl implements SwatchProductAttributeInterface
{
    /**
     * Add mapping data related to swatches.
     *
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     */
    public function __construct(DataInterface $configuration, EventManagerInterface $eventManager)
    {
        parent::__construct($configuration, $eventManager);
        $this->mappingData['frontend_input'] = [
            'Text Swatch' => 'swatch_text',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function changeStructureOfTheData(array $data): array
    {
        return parent::changeStructureOfTheData($data);
    }

    /**
     * Re-map options from default options structure to swatches structure,
     * as swatches was initially created with name convention differ from other attributes.
     *
     * @inheritdoc
     */
    protected function getSerializeOptions(array $data): string
    {
        $options = [];
        foreach ($data as $optionRowData) {
            $optionRowData['optiontext'] = $optionRowData['option'];
            $optionRowData['swatchtext'] = [
                'value' => $optionRowData['option']['value']
            ];
            unset($optionRowData['option']);
            $options[] = http_build_query($optionRowData);
        }

        return json_encode($options);
    }
}
