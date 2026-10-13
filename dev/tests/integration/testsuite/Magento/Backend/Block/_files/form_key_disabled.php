<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Backend\Model\UrlInterface::class
)->turnOffSecretKey();
