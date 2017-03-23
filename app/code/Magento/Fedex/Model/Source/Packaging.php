<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Fedex packaging source implementation
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Fedex\Model\Source;

class Packaging extends \Magento\Fedex\Model\Source\Generic
{
    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = 'packaging';
}
