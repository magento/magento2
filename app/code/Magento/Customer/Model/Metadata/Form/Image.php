<?php
/**
 * Form Element Image Data Model
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Framework\Service\ArrayObjectSearch;

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
            return array(__('"%1" is not a valid file.', $label));
        }

        $allowImageTypes = array(1 => 'gif', 2 => 'jpg', 3 => 'png');

        if (!isset($allowImageTypes[$imageProp[2]])) {
            return array(__('"%1" is not a valid image format.', $label));
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
        $errors = array();
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
