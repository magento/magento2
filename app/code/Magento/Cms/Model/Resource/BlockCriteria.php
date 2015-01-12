<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
