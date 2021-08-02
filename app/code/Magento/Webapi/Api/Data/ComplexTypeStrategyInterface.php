<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Api\Data;

use Magento\Webapi\Model\Soap\Wsdl;

/**
 * Interface ComplexTypeStrategyInterface
 */
interface ComplexTypeStrategyInterface
{
    /**
     * Method accepts the current WSDL context file.
     *
     * @param Wsdl $context
     *
     * @return void
     */
    public function setContext(Wsdl $context): void;

    /**
     * Create a complex type based on a strategy.
     *
     * @param  string $type
     *
     * @return string XSD type
     */
    public function addComplexType(string $type): string;
}
