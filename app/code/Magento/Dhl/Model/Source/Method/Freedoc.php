<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Model\Source\Method;

/**
 * Source model for DHL shipping methods for documentation
 * @since 2.0.0
 */
class Freedoc extends \Magento\Dhl\Model\Source\Method\AbstractMethod
{
    /**
     * Carrier Product Type Indicator
     *
     * @var string $_contentType
     * @since 2.0.0
     */
    protected $_contentType = \Magento\Dhl\Model\Carrier::DHL_CONTENT_TYPE_DOC;

    /**
     * Show 'none' in methods list or not;
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_noneMethod = true;
}
