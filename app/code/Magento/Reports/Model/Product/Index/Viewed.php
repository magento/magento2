<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Reports\Model\Product\Index;

/**
 * Catalog Viewed Product Index
 *
 * @method \Magento\Reports\Model\Resource\Product\Index\Viewed _getResource()
 * @method \Magento\Reports\Model\Resource\Product\Index\Viewed getResource()
 * @method \Magento\Reports\Model\Product\Index\Viewed setVisitorId(int $value)
 * @method \Magento\Reports\Model\Product\Index\Viewed setCustomerId(int $value)
 * @method int getProductId()
 * @method \Magento\Reports\Model\Product\Index\Viewed setProductId(int $value)
 * @method \Magento\Reports\Model\Product\Index\Viewed setStoreId(int $value)
 * @method string getAddedAt()
 * @method \Magento\Reports\Model\Product\Index\Viewed setAddedAt(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Viewed extends \Magento\Reports\Model\Product\Index\AbstractIndex
{
    /**
     * Cache key name for Count of product index
     *
     * @var string
     */
    protected $_countCacheKey = 'product_index_viewed_count';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Reports\Model\Resource\Product\Index\Viewed');
    }

    /**
     * Retrieve Exclude Product Ids List for Collection
     *
     * @return array
     */
    public function getExcludeProductIds()
    {
        $productIds = array();

        if ($this->_registry->registry('current_product')) {
            $productIds[] = $this->_registry->registry('current_product')->getId();
        }

        return $productIds;
    }
}
