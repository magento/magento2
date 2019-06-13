<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Query;

/**
 * Extension point for adding custom parameters to context object
 */
interface ContextParametersProcessorInterface
{
    /**
     * Extension point for adding custom parameters to context object
     *
     * @param ContextParametersInterface $contextParameters
     * @return ContextParametersInterface
     */
    public function execute(ContextParametersInterface $contextParameters): ContextParametersInterface;
}
