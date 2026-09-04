<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Plugin;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Cms\Model\Validator\DirectiveValidator;

class BlockRepositoryValidatePlugin
{
    /**
     * @var DirectiveValidator
     */
    private DirectiveValidator $validator;

    /**
     * @param DirectiveValidator $validator
     */
    public function __construct(DirectiveValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validate Cms block before save
     *
     * @param BlockRepositoryInterface $subject
     * @param BlockInterface $block
     * @return BlockInterface[]
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(BlockRepositoryInterface $subject, BlockInterface $block): array
    {
        if (!$this->validator->isValid((string)$block->getContent())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('CMS block contains invalid content — please review and correct the block.')
            );
        }
        return [$block];
    }
}
