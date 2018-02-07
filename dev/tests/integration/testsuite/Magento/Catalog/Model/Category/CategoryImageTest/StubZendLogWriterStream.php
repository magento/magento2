<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category\CategoryImageTest;

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    'Magento\Framework\App\Config\MutableScopeConfigInterface'
)->setValue(
    'dev/log/active',
    1,
    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
);

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    'Magento\Framework\App\Config\MutableScopeConfigInterface'
)->setValue(
    'dev/log/exception_file',
    'save_category_without_image.log',
    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
);
class StubZendLogWriterStream extends \Zend_Log_Writer_Stream
{
    /** @var array */
    public static $exceptions = [];

    public function write($event)
    {
        self::$exceptions[] = $event;

        parent::write($event);
    }
}
