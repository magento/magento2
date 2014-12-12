<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Cms\Model\Resource;

use Magento\Cms\Model\BlockCriteriaInterface;

/**
 * Class BlockCriteria
 */
class BlockCriteria extends CmsAbstractCriteria implements BlockCriteriaInterface
{
    /**
     * @param string $mapper
     */
    public function __construct($mapper = '')
    {
        $this->mapperInterfaceName = $mapper ?: 'Magento\Cms\Model\Resource\BlockCriteriaMapper';
    }
}
