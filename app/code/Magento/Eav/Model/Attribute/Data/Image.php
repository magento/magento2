<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute\Data;

/**
 * EAV Entity Attribute Image File Data Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Image extends \Magento\Eav\Model\Attribute\Data\File
{
    /**
     * Validate file by attribute validate rules
     * Return array of errors
     *
     * @param array $value
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _validateByRules($value)
    {
        $label = __($this->getAttribute()->getStoreLabel());
        $rules = $this->getAttribute()->getValidateRules();

        $imageProp = @getimagesize($value['tmp_name']);

        $allowImageTypes = [1 => 'gif', 2 => 'jpg', 3 => 'png'];

        if (!isset($allowImageTypes[$imageProp[2]])) {
            return [__('"%1" is not a valid image format', $label)];
        }

        // modify image name
        $extension = pathinfo($value['name'], PATHINFO_EXTENSION);
        if ($extension != $allowImageTypes[$imageProp[2]]) {
            $value['name'] = pathinfo($value['name'], PATHINFO_FILENAME) . '.' . $allowImageTypes[$imageProp[2]];
        }

        $errors = [];
        if (!empty($rules['max_file_size'])) {
            $size = $value['size'];
            if ($rules['max_file_size'] < $size) {
                $errors[] = __('"%1" exceeds the allowed file size.', $label);
            }
        }

        if (!empty($rules['max_image_width'])) {
            if ($rules['max_image_width'] < $imageProp[0]) {
                $r = $rules['max_image_width'];
                $errors[] = __('"%1" width exceeds allowed value of %2 px.', $label, $r);
            }
        }
        if (!empty($rules['max_image_heght'])) {
            if ($rules['max_image_heght'] < $imageProp[1]) {
                $r = $rules['max_image_heght'];
                $errors[] = __('"%1" height exceeds allowed value of %2 px.', $label, $r);
            }
        }

        return $errors;
    }
}
