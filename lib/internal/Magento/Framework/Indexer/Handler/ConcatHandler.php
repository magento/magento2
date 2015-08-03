<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Handler;

use Magento\Framework\Indexer\HandlerInterface;
use Magento\Framework\App\Resource\SourceProviderInterface;

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
     * @param SourceProviderInterface $source
     * @param string $alias
     * @param array $fieldInfo
     * @return void
     */
    public function prepareSql(SourceProviderInterface $source, $alias, $fieldInfo)
    {
        $source->getSelect()->columns([$fieldInfo['name'] => $this->concatExpression]);
    }

    /**
     * @param SourceProviderInterface $source
     * @param array $fieldInfo
     * @return void
     */
    public function prepareData(SourceProviderInterface $source, $fieldInfo)
    {

    }
}
