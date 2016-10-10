<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Handler\SwatchProductAttribute;

use Magento\Catalog\Test\Handler\CatalogProductAttribute\Curl as CatalogProductAttributeCurl;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\System\Event\EventManagerInterface;

/**
 * Class Curl
 * Curl handler for creating Swatch Attribute
 */
class Curl extends CatalogProductAttributeCurl implements SwatchProductAttributeInterface
{
    /**
     * Add mapping data related to swatches
     *
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     */
    public function __construct(DataInterface $configuration, EventManagerInterface $eventManager)
    {
        $this->mappingData['frontend_input'] = [
            'Text Swatch' => 'swatch_text',
        ];

        parent::__construct($configuration, $eventManager);
    }

    /**
     * Re-map options from default options structure to swatches structure
     *
     * @param array $data
     * @return array
     */
    protected function changeStructureOfTheData(array $data) {
        $data['optiontext'] = $data['option'];
        $data['swatchtext'] = [
            'value' => $data['option']['value']
        ];
        unset($data['option']);
        return $data;
    }
}
