<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options;

/**
 * Block Class for Visual Swatch
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
     * @codeCoverageIgnore
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
            $value['store' . $storeId] = $this->escapeHtml($storeValues[$optionId]);
        }

        if (isset($swatchStoreValue[$optionId])) {
            $value['defaultswatch' . $storeId] = $this->escapeHtml($swatchStoreValue[$optionId]);
        }

        $swatchStoreValue = $this->reformatSwatchLabels($swatchStoreValue);
        if (isset($swatchStoreValue[$optionId])) {
            $value['swatch' . $storeId] = $this->escapeHtml($swatchStoreValue[$optionId]);
        }

        return $value;
    }

    /**
     * Return json config for visual option JS initialization
     *
     * @return array
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
     * @param null $swatchStoreValue
     * @return string
     */
    protected function reformatSwatchLabels($swatchStoreValue = null)
    {
        if ($swatchStoreValue === null) {
            return;
        }
        $newSwatch = '';
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
