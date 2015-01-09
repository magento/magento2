<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Downloadable\Model\File;

use Magento\Framework\Exception\InputException;

class ContentValidator
{
    /**
     * Check if gallery entry content is valid
     *
     * @param Content $fileContent
     * @return bool
     * @throws InputException
     */
    public function isValid(Content $fileContent)
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
