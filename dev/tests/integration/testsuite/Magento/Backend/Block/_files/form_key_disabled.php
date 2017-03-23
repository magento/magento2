<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Backend\Model\UrlInterface::class
)->turnOffSecretKey();
