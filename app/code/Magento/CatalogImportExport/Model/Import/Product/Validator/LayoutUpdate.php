<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\View\Model\Layout\Update\ValidatorFactory;

/**
 * Validates layout and custom layout update fields
 */
class LayoutUpdate extends AbstractImportValidator
{
    /**
     * @var ValidatorFactory
     */
    private $layoutValidatorFactory;

    /**
     * @var ValidationStateInterface
     */
    private $validationState;

    /**
     * @param ValidatorFactory $layoutValidatorFactory
     * @param ValidationStateInterface $validationState
     */
    public function __construct(
        ValidatorFactory $layoutValidatorFactory,
        ValidationStateInterface $validationState
    ) {
        $this->layoutValidatorFactory = $layoutValidatorFactory;
        $this->validationState = $validationState;
    }

    /**
     * @inheritdoc
     */
    public function isValid($value): bool
    {
        if (!empty($value['custom_layout_update']) && !$this->validateXml($value['custom_layout_update'])) {
            $this->_addMessages(
                [
                    $this->context->retrieveMessageTemplate('invalidLayoutUpdate')
                ]
            );
            return false;
        }

        return true;
    }

    /**
     * Validate XML layout update
     *
     * @param string $xml
     * @return bool
     */
    private function validateXml(string $xml): bool
    {
        /** @var $layoutXmlValidator \Magento\Framework\View\Model\Layout\Update\Validator */
        $layoutXmlValidator = $this->layoutValidatorFactory->create(
            [
                'validationState' => $this->validationState,
            ]
        );

        try {
            if (!$layoutXmlValidator->isValid($xml)) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
