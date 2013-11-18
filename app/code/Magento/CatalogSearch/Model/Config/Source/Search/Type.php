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
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog search types
 *
 * @category   Magento
 * @package    Magento_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogSearch\Model\Config\Source\Search;

class Type implements \Magento\Core\Model\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $types = array(
            \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_LIKE     => 'Like',
            \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_FULLTEXT => 'Fulltext',
            \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_COMBINE  => 'Combine (Like and Fulltext)',
        );
        $options = array();
        foreach ($types as $k => $v) {
            $options[] = array(
                'value' => $k,
                'label' => $v
            );
        }
        return $options;
    }
}
