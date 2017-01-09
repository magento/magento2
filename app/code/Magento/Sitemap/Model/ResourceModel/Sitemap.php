<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model\ResourceModel;

/**
 * Sitemap resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Sitemap extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sitemap', 'sitemap_id');
    }
}
