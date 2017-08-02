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
class Unitofmeasure extends \Magento\Dhl\Model\Source\Method\Generic
{
    /**
     * Carrier code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_code = 'unit_of_measure';
}
