<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\PageRepository\Validator;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageRepository\ValidatorInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\HTML\WYSIWYGValidatorInterface;

/**
 * Validates pages' content.
 */
class ContentValidator implements ValidatorInterface
{

    /**
     * @var WYSIWYGValidatorInterface
     */
    private $wysiwygValidator;

    /**
     * @param WYSIWYGValidatorInterface $wysiwygValidator
     */
    public function __construct(WYSIWYGValidatorInterface $wysiwygValidator)
    {
        $this->wysiwygValidator = $wysiwygValidator;
    }

    /**
     * @inheritDoc
     */
    public function validate(PageInterface $page): void
    {
        $oldValue = null;
        if ($page->getId() && $page instanceof Page && $page->getOrigData()) {
            $oldValue = $page->getOrigData(PageInterface::CONTENT);
        }

        if ($page->getContent() && $page->getContent() !== $oldValue) {
            try {
                $this->wysiwygValidator->validate($page->getContent());
            } catch (ValidationException $exception) {
                throw new ValidationException(
                    __('Content HTML contains restricted elements. %1', $exception->getMessage()),
                    $exception
                );
            }
        }
    }
}
