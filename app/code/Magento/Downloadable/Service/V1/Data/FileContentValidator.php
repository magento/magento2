<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\Data;

use Magento\Framework\Exception\InputException;

class FileContentValidator
{
    /**
     * Check if gallery entry content is valid
     *
     * @param FileContent $fileContent
     * @return bool
     * @throws InputException
     */
    public function isValid(FileContent $fileContent)
    {
        $decodedContent = @base64_decode($fileContent->getData(), true);
        if (empty($decodedContent)) {
            throw new InputException('Provided content must be valid base64 encoded data.');
        }

        if (!$this->isFileNameValid($fileContent->getName())) {
            throw new InputException('Provided file name contains forbidden characters.');
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
