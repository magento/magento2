<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\LayeredNavigation\Model\Aggregation;

use Magento\Framework\Search\Request\Aggregation\StatusInterface;

class Status implements StatusInterface
{
    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
    }
}
