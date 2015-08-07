<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractImportValidator;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

class Media extends AbstractImportValidator implements RowValidatorInterface
{
    const URL_REGEXP = '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i';

    const PATH_REGEXP = '#^(\w+/){1,2}\w+\.\w+$#';

    const ADDITIONAL_IMAGES = 'additional_images';

    const ADDITIONAL_IMAGES_DELIMITER = ',';

    /** @var array */
    protected $media_attributes = ['image', 'small_image', 'thumbnail'];

    /**
     * {@inheritdoc}
     */
    public function init($context)
    {
        return parent::init($context);
    }

    /**
     * @param $string
     * @return bool
     */
    public function checkValidUrl($string)
    {
        return preg_match(self::URL_REGEXP, $string);
    }

    /**
     * @param $string
     * @return bool
     */
    public function checkPath($string)
    {
        return preg_match(self::PATH_REGEXP, $string);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        $valid = true;
        foreach ($this->media_attributes as $attribute) {
            if (isset($value[$attribute]) && strlen($value[$attribute])) {
                if (!$this->checkPath($value[$attribute]) && !$this->checkValidUrl($value[$attribute])) {
                    $this->_addMessages([
                        sprintf($this->context->retrieveMessageTemplate(self::ERROR_INVALID_MEDIA_URL), $attribute)
                    ]);
                    $valid = false;
                }
            }
        }
        if (isset($value[self::ADDITIONAL_IMAGES]) && strlen($value[self::ADDITIONAL_IMAGES])) {
            foreach (explode(self::ADDITIONAL_IMAGES_DELIMITER, $value[self::ADDITIONAL_IMAGES]) as $image) {
                if (!$this->checkPath($image) && !$this->checkValidUrl($image)) {
                    $this->_addMessages([
                        sprintf($this->context->retrieveMessageTemplate(self::ERROR_INVALID_MEDIA_URL), self::ADDITIONAL_IMAGES)
                    ]);
                }
                break;
            }
        }
        return $valid;
    }
}
