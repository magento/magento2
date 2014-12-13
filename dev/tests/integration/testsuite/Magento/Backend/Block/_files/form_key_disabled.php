<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    'Magento\Backend\Model\UrlInterface'
)->turnOffSecretKey();
