<?php
/**
 * Form Element Image Data Model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Framework\Api\ArrayObjectSearch;

class Image extends File
{
    /**
     * Validate file by attribute validate rules
     * Return array of errors
     *
     * @param array $value
     * @return string[]
     */
    protected function _validateByRules($value)
    {
        $label = $value['name'];
        $rules = $this->getAttribute()->getValidationRules();

        $imageProp = @getimagesize($value['tmp_name']);

        if (!$this->_isUploadedFile($value['tmp_name']) || !$imageProp) {
            return [__('"%1" is not a valid file.', $label)];
        }

        $allowImageTypes = [1 => 'gif', 2 => 'jpg', 3 => 'png'];

        if (!isset($allowImageTypes[$imageProp[2]])) {
            return [__('"%1" is not a valid image format.', $label)];
        }

        // modify image name
        $extension = pathinfo($value['name'], PATHINFO_EXTENSION);
        if ($extension != $allowImageTypes[$imageProp[2]]) {
            $value['name'] = pathinfo($value['name'], PATHINFO_FILENAME) . '.' . $allowImageTypes[$imageProp[2]];
        }

        $maxFileSize = ArrayObjectSearch::getArrayElementByName(
            $rules,
            'max_file_size'
        );
        $errors = [];
        if (!is_null($maxFileSize)) {
            $size = $value['size'];
            if ($maxFileSize < $size) {
                $errors[] = __('"%1" exceeds the allowed file size.', $label);
            }
        }

        $maxImageWidth = ArrayObjectSearch::getArrayElementByName(
            $rules,
            'max_image_width'
        );
        if (!is_null($maxImageWidth)) {
            if ($maxImageWidth < $imageProp[0]) {
                $r = $maxImageWidth;
                $errors[] = __('"%1" width exceeds allowed value of %2 px.', $label, $r);
            }
        }

        $maxImageHeight = ArrayObjectSearch::getArrayElementByName(
            $rules,
            'max_image_height'
        );
        if (!is_null($maxImageHeight)) {
            if ($maxImageHeight < $imageProp[1]) {
                $r = $maxImageHeight;
                $errors[] = __('"%1" height exceeds allowed value of %2 px.', $label, $r);
            }
        }

        return $errors;
    }
}
