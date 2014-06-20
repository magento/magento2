<?php
/**
 * Product Media Content Validator
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
namespace Magento\Catalog\Service\V1\Product\Attribute\Media\Data;

use \Magento\Framework\Exception\InputException;

class GalleryEntryContentValidator
{
    /**
     * @var array
     */
    private $defaultMimeTypes = array(
        'image/jpg',
        'image/jpeg',
        'image/gif',
        'image/png',
    );

    /**
     * @var array
     */
    private $allowedMimeTypes;

    /**
     * @param array $allowedMimeTypes
     */
    public function __construct(
        array $allowedMimeTypes = array()
    ) {
        $this->allowedMimeTypes = array_merge($this->defaultMimeTypes, $allowedMimeTypes);
    }

    /**
     * Check if gallery entry content is valid
     *
     * @param GalleryEntryContent $entryContent
     * @return bool
     * @throws InputException
     */
    public function isValid(GalleryEntryContent $entryContent)
    {
        $fileContent = @base64_decode($entryContent->getData(), true);
        if (empty($fileContent)) {
            throw new InputException('The image content must be valid base64 encoded data.');
        }
        $imageProperties = @getimagesizefromstring($fileContent);
        if (empty($imageProperties)) {
            throw new InputException('The image content must be valid base64 encoded data.');
        }
        $sourceMimeType = $imageProperties['mime'];
        if ($sourceMimeType != $entryContent->getMimeType() || !$this->isMimeTypeValid($sourceMimeType)) {
            throw new InputException('The image MIME type is not valid or not supported.');
        }
        if (!$this->isNameValid($entryContent->getName())) {
            throw new InputException('Provided image name contains forbidden characters.');
        }
        return true;
    }

    /**
     * Check if given mime type is valid
     *
     * @param string $mimeType
     * @return bool
     */
    protected function isMimeTypeValid($mimeType)
    {
        return in_array($mimeType, $this->allowedMimeTypes);
    }

    /**
     * Check if given filename is valid
     *
     * @param string $name
     * @return bool
     */
    protected function isNameValid($name)
    {
        // Cannot contain \ / : * ? " < > |
        if (!preg_match('/^[^\\/?*:";<>()|{}\\\\]+$/', $name)) {
            return false;
        }
        return true;
    }
}
