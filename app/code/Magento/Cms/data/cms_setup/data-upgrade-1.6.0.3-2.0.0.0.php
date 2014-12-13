<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var $this \Magento\Cms\Model\Resource\Setup */

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
