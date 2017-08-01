<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Fedex method source implementation
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Fedex\Model\Source;

/**
 * Class \Magento\Fedex\Model\Source\Method
 *
 * @since 2.0.0
 */
class Method extends \Magento\Fedex\Model\Source\Generic
{
    /**
     * Carrier code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_code = 'method';
}
