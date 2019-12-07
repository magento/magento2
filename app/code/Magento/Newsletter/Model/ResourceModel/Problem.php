<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\ResourceModel;

/**
 * Newsletter problem resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @api
 * @since 100.0.2
 */
class Problem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('newsletter_problem', 'problem_id');
    }
}
