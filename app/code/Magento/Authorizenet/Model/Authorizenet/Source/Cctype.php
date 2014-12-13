<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Authorizenet\Model\Authorizenet\Source;

/**
 * Authorize.net Payment CC Types Source Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return string[]
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'OT'];
    }
}
