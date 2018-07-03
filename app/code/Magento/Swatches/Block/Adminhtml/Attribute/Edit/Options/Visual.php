<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options;

/**
 * Block Class for Visual Swatch
 *
 * @api
 * @since 100.0.2
 */
class Visual extends AbstractSwatch
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Swatches::catalog/product/attribute/visual.phtml';

    /**
     * Create store values
     *
     * Method not intended to escape HTML entities
     * Escaping will be applied in template files
     *
     * @param integer $storeId
     * @param integer $optionId
     * @return array
     */
    protected function createStoreValues($storeId, $optionId)
    {
        $value = [];
        $value['store' . $storeId] = '';
        $value['defaultswatch' . $storeId] = '';
        $value['swatch' . $storeId] = '';
        $storeValues = $this->getStoreOptionValues($storeId);
        $swatchStoreValue = null;

        if (isset($storeValues['swatch'])) {
            $swatchStoreValue = $storeValues['swatch'];
        }

        if (isset($storeValues[$optionId])) {
            $value['store' . $storeId] = $storeValues[$optionId];
        }

        if (isset($swatchStoreValue[$optionId])) {
            $value['defaultswatch' . $storeId] = $swatchStoreValue[$optionId];
        }

        $swatchStoreValue = $this->reformatSwatchLabels($swatchStoreValue);
        if (isset($swatchStoreValue[$optionId])) {
            $value['swatch' . $storeId] = $swatchStoreValue[$optionId];
        }

        return $value;
    }

    /**
     * Return json config for visual option JS initialization
     *
     * @return array
     * @since 100.1.0
     */
    public function getJsonConfig()
    {
        $values = [];
        foreach ($this->getOptionValues() as $value) {
            $values[] = $value->getData();
        }

        $data = [
            'attributesData' => $values,
            'uploadActionUrl' => $this->getUrl('swatches/iframe/show'),
            'isSortable' => (int)(!$this->getReadOnly() && !$this->canManageOptionDefaultOnly()),
            'isReadOnly' => (int)$this->getReadOnly()
        ];

        return json_encode($data);
    }

    /**
     * Parse swatch labels for template
     *
     * @codeCoverageIgnore
     * @param null|array $swatchStoreValue
     * @return null|array
     */
    protected function reformatSwatchLabels($swatchStoreValue = null)
    {
        if ($swatchStoreValue === null) {
            return;
        }
        $newSwatch = [];
        foreach ($swatchStoreValue as $key => $value) {
            if ($value[0] == '#') {
                $newSwatch[$key] = 'background: '.$value;
            } elseif ($value[0] == '/') {
                $mediaUrl = $this->swatchHelper->getSwatchMediaUrl();
                $newSwatch[$key] = 'background: url('.$mediaUrl.$value.'); background-size: cover;';
            }
        }
        return $newSwatch;
    }
}
