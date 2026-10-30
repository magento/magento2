<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
