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
/** @var \Magento\Cms\Model\Resource\Setup $this */

$cookieRestriction = $this->createPage()->load('privacy-policy-cookie-restriction-mode', 'identifier');

if ($cookieRestriction->getId()) {
    $content = $cookieRestriction->getContent();
    $replacment = '{{config path="general/store_information/street_line1"}} ' .
        '{{config path="general/store_information/street_line2"}} ' .
        '{{config path="general/store_information/city"}} ' .
        '{{config path="general/store_information/postcode"}} ' .
        '{{config path="general/store_information/region_id"}} ' .
        '{{config path="general/store_information/country_id"}}';
    $content = preg_replace('/{{config path="general\\/store_information\\/address"}}/ims', $replacment, $content);
    $cookieRestriction->setContent($content)->save();
}
