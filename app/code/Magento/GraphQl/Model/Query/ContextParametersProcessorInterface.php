<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Query;

/**
 * Adding custom parameters to context object:
 *
 * - Add new processors argument item to ContextFactory in the di.xml.
 * - Class must extend ContextParametersProcessorInterface.
 * - Implement execute method which adds additional data to the context though extension attributes.
 * - This data will be present in each resolver.
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
