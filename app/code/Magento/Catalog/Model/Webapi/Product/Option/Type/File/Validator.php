<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Webapi\Product\Option\Type\File;

class Validator extends \Magento\Catalog\Model\Product\Option\Type\File\ValidatorInfo
{
    /**
     * @param array $optionValue
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate($optionValue, $option)
    {
        if (!is_array($optionValue)) {
            return false;
        }

        $this->fileFullPath = null;
        $this->fileRelativePath = null;
        $this->initFilePath($optionValue);

        if ($this->fileFullPath === null) {
            return false;
        }

        $validatorChain = $this->validateFactory->create();
        try {
            $validatorChain = $this->buildImageValidator($validatorChain, $option, $this->fileFullPath);
        } catch (\Magento\Framework\Exception\InputException $notImage) {
            return false;
        }

        $result = false;
        if ($validatorChain->isValid($this->fileFullPath)
            && $this->rootDirectory->isReadable($this->fileRelativePath)
        ) {
            $result = true;
        } elseif ($validatorChain->getErrors()) {
            $errors = $this->getValidatorErrors($validatorChain->getErrors(), $optionValue, $option);
            throw new \Magento\Framework\Exception\LocalizedException(__(implode("\n", $errors)));
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please specify product\'s required option(s).')
            );
        }
        return $result;
    }
}
