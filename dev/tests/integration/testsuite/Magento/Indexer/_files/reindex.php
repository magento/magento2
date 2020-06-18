<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$appDir = dirname(\Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppTempDir());
// phpcs:ignore Magento2.Security.InsecureFunction
exec("php -f {$appDir}/bin/magento indexer:reindex");
