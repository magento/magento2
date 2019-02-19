<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config;

/**
 * GraphQL config element.
 */
interface ConfigElementInterface
{
    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @return string
     */
    public function getDescription() : string;
}
