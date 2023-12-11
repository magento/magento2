<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestModuleSimpleTemplateDirective\Model;

use Magento\Framework\Filter\Template;

/**
 * A legacy directive test entity
 */
class LegacyFilter extends Template
{
    /**
     * Filter a directive
     *
     * @param $construction
     * @return string
     */
    protected function coolDirective($construction)
    {
        return 'value1: ' . $construction[1] . ':' . $construction[2];
    }

    /**
     * Filter a directive
     *
     * @param $construction
     * @return string
     */
    public function coolerDirective($construction)
    {
        return 'value2: ' . $construction[1] . ':' . $construction[2];
    }
}
