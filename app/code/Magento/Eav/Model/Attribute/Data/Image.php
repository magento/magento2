<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute\Data;

use Magento\Framework\Filesystem\ExtendedDriverInterface;

/**
 * EAV Entity Attribute Image File Data Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Image extends \Magento\Eav\Model\Attribute\Data\File
{
    /**
     * Validate file by attribute validate rules
     *
     * Return array of errors
     *
     * @param array $value
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _validateByRules($value)
    {
        $label = __($this->getAttribute()->getStoreLabel());
        $rules = $this->getAttribute()->getValidateRules();
        $localStorage = !$this->_directory->getDriver() instanceof ExtendedDriverInterface;
        $imageProp = $localStorage
            ? @getimagesize($value['tmp_name'])
            : $this->_directory->getDriver()->getMetadata($value['tmp_name']);
        $allowImageTypes = ['gif', 'jpg', 'jpeg', 'png'];
        if (!isset($imageProp['extension']) && isset($imageProp[2])) {
            $extensionsMap = [1 => 'gif', 2 => 'jpg', 3 => 'png'];
            $imageProp['extension'] = $extensionsMap[$imageProp[2]] ?? null;
        }

        if (!\in_array($imageProp['extension'], $allowImageTypes, true)) {
            return [__('"%1" is not a valid image format', $label)];
        }

        // modify image name
        $extension = pathinfo($value['name'], PATHINFO_EXTENSION);
        if ($extension !== $imageProp['extension']) {
            $value['name'] = pathinfo($value['name'], PATHINFO_FILENAME) . '.' . $imageProp['extension'];
        }

        $errors = [];
        if (!empty($rules['max_file_size'])) {
            $size = $value['size'];
            if ($rules['max_file_size'] < $size) {
                $errors[] = __('"%1" exceeds the allowed file size.', $label);
            }
        }

        $imageWidth = $imageProp['extra']['image-width'] ?? $imageProp[0];
        if (!empty($rules['max_image_width']) && !empty($imageWidth)
            && ($rules['max_image_width'] < $imageWidth)) {
            $r = $rules['max_image_width'];
            $errors[] = __('"%1" width exceeds allowed value of %2 px.', $label, $r);
        }
        $imageHeight = $imageProp['extra']['image-height'] ?? $imageProp[1];
        if (!empty($rules['max_image_height']) && !empty($imageHeight)
            && ($rules['max_image_height'] < $imageHeight)) {
            $r = $rules['max_image_height'];
            $errors[] = __('"%1" height exceeds allowed value of %2 px.', $label, $r);
        }

        return $errors;
    }
}
