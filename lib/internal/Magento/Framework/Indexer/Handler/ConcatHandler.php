<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Handler;

use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Magento\Framework\Indexer\HandlerInterface;

class ConcatHandler implements HandlerInterface
{
    /**
     * @var \Magento\Framework\DB\ConcatExpression
     */
    protected $concatExpression;

    /**
     * @param \Zend_Db_Expr $concatExpression
     */
    public function __construct(
        \Zend_Db_Expr $concatExpression
    ) {
        $this->concatExpression = $concatExpression;
    }

    /**
     * Prepare SQL for field and add it to collection
     *
     * @param SourceProviderInterface $source
     * @param string $alias
     * @param array $fieldInfo
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareSql(SourceProviderInterface $source, $alias, $fieldInfo)
    {
        $source->getSelect()->columns([$fieldInfo['name'] => $this->concatExpression]);
    }
}
