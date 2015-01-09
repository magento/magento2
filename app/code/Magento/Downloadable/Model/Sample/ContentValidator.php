<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Downloadable\Model\Sample;

use Magento\Downloadable\Api\Data\SampleContentInterface;
use Magento\Downloadable\Model\File\ContentValidator as FileContentValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Url\Validator as UrlValidator;

class ContentValidator
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
     * @param SampleContentInterface $sampleContent
     * @return bool
     * @throws InputException
     */
    public function isValid(SampleContentInterface $sampleContent)
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
     * @param SampleContentInterface $sampleContent
     * @throws InputException
     * @return void
     */
    protected function validateSampleResource(SampleContentInterface $sampleContent)
    {
        $sampleFile = $sampleContent->getSampleFile();
        if ($sampleContent->getSampleType() == 'file'
            && (!$sampleFile || !$this->fileContentValidator->isValid($sampleFile))
        ) {
            throw new InputException('Provided file content must be valid base64 encoded data.');
        }

        $a = $sampleContent->getSampleUrl();
        if ($sampleContent->getSampleType() == 'url'
            && !$this->urlValidator->isValid($sampleContent->getSampleUrl())
        ) {
            throw new InputException('Sample URL must have valid format.');
        }
    }
}
