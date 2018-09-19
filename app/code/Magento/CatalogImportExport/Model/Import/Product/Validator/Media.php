<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url\Validator;

class Media extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @deprecated As this regexp doesn't give guarantee of correct url validation
     * @see \Magento\Framework\Url\Validator::isValid()
     */
    const URL_REGEXP = '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i';

    const PATH_REGEXP = '#^(?!.*[\\/]\.{2}[\\/])(?!\.{2}[\\/])[-\w.\\/]+$#';

    const ADDITIONAL_IMAGES = 'additional_images';

    /**
     * The url validator. Checks if given url is valid.
     *
     * @var Validator
     */
    private $validator;

    /**
     * @param Validator $validator The url validator
     */
    public function __construct(Validator $validator = null)
    {
        $this->validator = $validator ?: ObjectManager::getInstance()->get(Validator::class);
    }

    /**
     * @deprecated
     * @see \Magento\CatalogImportExport\Model\Import\Product::getMultipleValueSeparator()
     */
    const ADDITIONAL_IMAGES_DELIMITER = ',';

    /**
     * @var array
     */
    protected $mediaAttributes = ['image', 'small_image', 'thumbnail'];

    /**
     * @param string $string
     * @return bool
     * @deprecated 100.2.0 As this method doesn't give guarantee of correct url validation.
     * @see \Magento\Framework\Url\Validator::isValid() It provides better url validation.
     */
    protected function checkValidUrl($string)
    {
        return preg_match(self::URL_REGEXP, $string);
    }

    /**
     * @param string $string
     * @return bool
     */
    protected function checkPath($string)
    {
        return preg_match(self::PATH_REGEXP, $string);
    }

    /**
     * @param string $path
     * @return bool
     */
    protected function checkFileExists($path)
    {
        return file_exists($path);
    }

    /**
     * Validate value
     *
     * @param array $value
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        $valid = true;
        foreach ($this->mediaAttributes as $attribute) {
            if (isset($value[$attribute]) && strlen($value[$attribute])) {
                if (!$this->checkPath($value[$attribute]) && !$this->validator->isValid($value[$attribute])) {
                    $this->_addMessages(
                        [
                            sprintf(
                                $this->context->retrieveMessageTemplate(self::ERROR_INVALID_MEDIA_URL_OR_PATH),
                                $attribute
                            )
                        ]
                    );
                    $valid = false;
                }
            }
        }
        if (isset($value[self::ADDITIONAL_IMAGES]) && strlen($value[self::ADDITIONAL_IMAGES])) {
            foreach (explode($this->context->getMultipleValueSeparator(), $value[self::ADDITIONAL_IMAGES]) as $image) {
                if (!$this->checkPath($image) && !$this->validator->isValid($image)) {
                    $this->_addMessages(
                        [
                            sprintf(
                                $this->context->retrieveMessageTemplate(self::ERROR_INVALID_MEDIA_URL_OR_PATH),
                                self::ADDITIONAL_IMAGES
                            )
                        ]
                    );
                    $valid = false;
                }
                break;
            }
        }
        return $valid;
    }
}
