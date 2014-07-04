<?php
/**
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
namespace Magento\Downloadable\Service\V1\DownloadableSample\Data;

use \Magento\Downloadable\Service\V1\Data\FileContentValidator;
use \Magento\Framework\Url\Validator as UrlValidator;
use \Magento\Framework\Exception\InputException;

class DownloadableSampleContentValidator
{
    /**
     * @var UrlValidator
     */
    protected $urlValidator;

    /**
     * @var FileContentValidator
     */
    protected $fileContentValidator;

    /**
     * @param FileContentValidator $fileContentValidator
     * @param UrlValidator $urlValidator
     */
    public function __construct(
        FileContentValidator $fileContentValidator,
        UrlValidator $urlValidator
    ) {
        $this->fileContentValidator = $fileContentValidator;
        $this->urlValidator = $urlValidator;
    }

    /**
     * Check if sample content is valid
     *
     * @param DownloadableSampleContent $sampleContent
     * @return bool
     * @throws InputException
     */
    public function isValid(DownloadableSampleContent $sampleContent)
    {
        if (!is_int($sampleContent->getSortOrder()) || $sampleContent->getSortOrder() < 0) {
            throw new InputException('Sort order must be a positive integer.');
        }

        $this->validateSampleResource($sampleContent);
        return true;
    }

    /**
     * Validate sample resource (file or URL)
     *
     * @param DownloadableSampleContent $sampleContent
     * @throws InputException
     * @return void
     */
    protected function validateSampleResource(DownloadableSampleContent $sampleContent)
    {
        $sampleFile = $sampleContent->getSampleFile();
        if ($sampleContent->getSampleType() == 'file'
            && (!$sampleFile || !$this->fileContentValidator->isValid($sampleFile))
        ) {
            throw new InputException('Provided file content must be valid base64 encoded data.');
        }

        if ($sampleContent->getSampleType() == 'url'
            && !$this->urlValidator->isValid($sampleContent->getSampleUrl())
        ) {
            throw new InputException('Sample URL must have valid format.');
        }
    }
}
