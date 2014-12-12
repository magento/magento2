<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Data\Stub;

use Magento\Framework\Data\AbstractSearchCriteriaBuilder;

class SearchCriteriaBuilder extends AbstractSearchCriteriaBuilder
{
    /**
     * @return string|void
     */
    public function init()
    {
        $this->resultObjectInterface = 'Magento\Framework\Api\CriteriaInterface';
    }
}
