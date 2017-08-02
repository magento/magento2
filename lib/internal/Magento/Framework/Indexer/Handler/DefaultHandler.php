<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Handler;

use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Magento\Framework\Indexer\HandlerInterface;

/**
 * Class \Magento\Framework\Indexer\Handler\DefaultHandler
 *
 * @since 2.0.0
 */
class DefaultHandler implements HandlerInterface
{
    /**
     * @param SourceProviderInterface $source
     * @param string $alias
     * @param array $fieldInfo
     * @return void
     * @since 2.0.0
     */
    public function prepareSql(SourceProviderInterface $source, $alias, $fieldInfo)
    {
        $source->getSelect()->columns($fieldInfo['origin'] . ' AS ' . $fieldInfo['name'], $alias);
    }
}
