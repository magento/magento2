<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\PageRepository\Validator;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\PageRepository\ValidatorInterface;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Config\Dom\ValidationSchemaException;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Model\Layout\Update\Validator;
use Magento\Framework\View\Model\Layout\Update\ValidatorFactory;

/**
 * Validate a given page
 */
class LayoutUpdateValidator implements ValidatorInterface
{
    /**
     * @var ValidatorFactory
     */
    private $validatorFactory;

    /**
     * @var ValidationStateInterface
     */
    private $validationState;

    /**
     * @param ValidatorFactory $validatorFactory
     * @param ValidationStateInterface $validationState
     */
    public function __construct(
        ValidatorFactory $validatorFactory,
        ValidationStateInterface $validationState
    ) {
        $this->validatorFactory = $validatorFactory;
        $this->validationState = $validationState;
    }

    /**
     * Validate the data before saving
     *
     * @param PageInterface $page
     * @throws LocalizedException
     */
    public function validate(PageInterface $page): void
    {
        $this->validateRequiredFields($page);
        $this->validateLayoutUpdate($page);
        $this->validateCustomLayoutUpdate($page);
    }

    /**
     * Validate required fields
     *
     * @param PageInterface $page
     * @throws LocalizedException
     */
    private function validateRequiredFields(PageInterface $page): void
    {
        if (empty($page->getTitle())) {
            throw new LocalizedException(__('Required field "%1" is empty.', 'title'));
        }
    }

    /**
     * Validate layout update
     *
     * @param PageInterface $page
     * @throws LocalizedException
     */
    private function validateLayoutUpdate(PageInterface $page): void
    {
        $layoutXmlValidator = $this->getLayoutValidator();

        try {
            if (!empty($page->getLayoutUpdateXml())
                && !$layoutXmlValidator->isValid($page->getLayoutUpdateXml())
            ) {
                throw new LocalizedException(__('Layout update is invalid'));
            }
        } catch (ValidationException|ValidationSchemaException $e) {
            throw new LocalizedException(__('Layout update is invalid'));
        }
    }

    /**
     * Validate custom layout update
     *
     * @param PageInterface $page
     * @throws LocalizedException
     */
    private function validateCustomLayoutUpdate(PageInterface $page): void
    {
        $layoutXmlValidator = $this->getLayoutValidator();

        try {
            if (!empty($page->getCustomLayoutUpdateXml())
                && !$layoutXmlValidator->isValid($page->getCustomLayoutUpdateXml())
            ) {
                throw new LocalizedException(__('Custom layout update is invalid'));
            }
        } catch (ValidationException|ValidationSchemaException $e) {
            throw new LocalizedException(__('Custom layout update is invalid'));
        }
    }

    /**
     * Return a new validator
     *
     * @return Validator
     */
    private function getLayoutValidator(): Validator
    {
        return $this->validatorFactory->create(
            [
                'validationState' => $this->validationState,
            ]
        );
    }
}
