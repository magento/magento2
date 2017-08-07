<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\File;

use Magento\Downloadable\Api\Data\File\ContentInterface;
use Magento\Framework\Exception\InputException;

/**
 * Class \Magento\Downloadable\Model\File\ContentValidator
 *
 */
class ContentValidator
{
    /**
     * Check if gallery entry content is valid
     *
     * @param ContentInterface $fileContent
     * @throws InputException
     * @return bool
     */
    public function isValid(ContentInterface $fileContent)
    {
        $decodedContent = @base64_decode($fileContent->getFileData(), true);
        if (empty($decodedContent)) {
            throw new InputException(__('Provided content must be valid base64 encoded data.'));
        }

        if (!$this->isFileNameValid($fileContent->getName())) {
            throw new InputException(__('Provided file name contains forbidden characters.'));
        }
        return true;
    }

    /**
     * Check if given filename is valid
     *
     * @param string $fileName
     * @return bool
     */
    protected function isFileNameValid($fileName)
    {
        // Cannot contain \ / : * ? " < > |
        if (!preg_match('/^[^\\/?*:";<>()|{}\\\\]+$/', $fileName)) {
            return false;
        }
        return true;
    }
}
