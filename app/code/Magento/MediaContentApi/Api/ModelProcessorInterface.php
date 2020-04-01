<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\Framework\Model\AbstractModel;

/**
 * Save relations for content within an AbstractModel instance
 * @api
 */
interface ModelProcessorInterface
{
    /**
     * Save relations between content and media files within an AbstractModel instance
     *
     * @param string $type
     * @param AbstractModel $model
     * @param array $fields
     */
    public function execute(string $type, AbstractModel $model, array $fields): void;
}
