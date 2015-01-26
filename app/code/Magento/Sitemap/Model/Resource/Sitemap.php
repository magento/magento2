<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model\Resource;

/**
 * Sitemap resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Sitemap extends \Magento\Framework\Model\Resource\Db\AbstractDb
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
