<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    public function __construct(
        DataInterface $configuration,
        EventManagerInterface $eventManager
    ) {
        parent::__construct($configuration, $eventManager);
        $this->mappingData['frontend_input'] = [
            'Text Swatch' => 'swatch_text',
        ];
    }

    /**
     * Re-map options from default options structure to swatches structure,
     * as swatches was initially created with name convention differ from other attributes.
     *
     * @param array $data
     * @return array
     */
    protected function changeStructureOfTheData(array $data)
    {
        /** @var array $data */
        $data = parent::changeStructureOfTheData($data);
        $data['optiontext'] = $data['option'];
        $data['swatchtext'] = [
            'value' => $data['option']['value'],
        ];

        unset($data['option']);

        return $data;
    }
}
