<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Save relations for content within an AbstractModel instance
 */
class ModelProcessor implements ModelProcessorInterface
{
    /**
     * Content processor
     *
     * @var ContentProcessor
     */
    private $contentProcessor;

    /**
     * @param ContentProcessor $contentProcessor
     */
    public function __construct(
        ContentProcessor $contentProcessor
    ) {
        $this->contentProcessor = $contentProcessor;
    }

    /**
     * Save relations for content within an AbstractModel instance
     *
     * @param string $type
     * @param AbstractModel $model
     * @param array $fields
     */
    public function execute(string $type, AbstractModel $model, array $fields): void
    {
        foreach ($fields as $field) {
            if (!$model->dataHasChangedFor($field)) {
                continue;
            }
            $this->contentProcessor->execute(
                $type,
                $field,
                (string) $model->getId(),
                (string) $model->getData($field)
            );
        }
    }
}
