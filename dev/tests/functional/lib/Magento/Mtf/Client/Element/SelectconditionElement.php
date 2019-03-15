<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Mtf\Client\Element;

/**
 * @inheritdoc
 */
class SelectconditionElement extends SelectElement
{
    /**
     * @inheritdoc
     */
    protected $optionByValue = './/option[normalize-space(.)=%s]';
}
