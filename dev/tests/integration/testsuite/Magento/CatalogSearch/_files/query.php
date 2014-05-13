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
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $query \Magento\CatalogSearch\Model\Query */
$query = $objectManager->create('Magento\CatalogSearch\Model\Query');
$query->setStoreId(1);
$query->setQueryText(
    'query_text'
)->setNumResults(
    1
)->setPopularity(
    1
)->setDisplayInTerms(
    1
)->setIsActive(
    1
)->setIsProcessed(
    1
)->save();
