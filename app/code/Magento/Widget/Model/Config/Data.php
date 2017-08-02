<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Config;

/**
 * Class \Magento\Widget\Model\Config\Data
 *
 * @since 2.0.0
 */
class Data extends \Magento\Framework\Config\Data\Scoped
{
    /**
     * Scope priority loading scheme
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_scopePriorityScheme = ['global', 'design'];
}
