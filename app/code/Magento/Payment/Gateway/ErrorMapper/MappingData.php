<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Gateway\ErrorMapper;

use Magento\Framework\Config\Data\Scoped;

/**
 * Extends Scoped class to override `_scopePriorityScheme` property.
 * It allows to load and merge config files from `global` scope and current scope to a single structure.
 */
class MappingData extends Scoped
{
    /**
     * @inheritdoc
     */
    protected $_scopePriorityScheme = ['global'];
}
