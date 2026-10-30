<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\TestModuleControllerBackpressure\Model;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Backpressure\RequestTypeExtractorInterface;
use Magento\Framework\App\RequestInterface;

class TypeExtractor implements RequestTypeExtractorInterface
{
    /**
     * @inheritDoc
     */
    public function extract(RequestInterface $request, ActionInterface $action): ?string
    {
        if ($action instanceof Index) {
            return 'testcontrollerbackpressure';
        }

        return null;
    }
}
