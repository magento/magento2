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
 * @package     Mage_DesignEditor
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
?>
<?php
return array(
    array(
        'label' => 'All Pages',
        'url'   => 'http://localhost/index.php/design/editor/page/handle/default/',
    ),
    array(
        'label' => 'Catalog Category (Non-Anchor)',
        'url'   => 'http://localhost/index.php/design/editor/page/handle/catalog_category_default/',
    ),
    array(
        'label' => 'Catalog Product View (Any)',
        'url'   => 'http://localhost/index.php/design/editor/page/handle/catalog_product_view/',
    ),
    array(
        'label' => 'Catalog Product View (Simple)',
        'url'   => '',
    ),
);
