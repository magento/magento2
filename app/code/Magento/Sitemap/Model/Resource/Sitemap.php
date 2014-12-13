<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
