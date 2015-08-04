<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Handler;

use Magento\Framework\App\Resource\SourceProviderInterface;
use Magento\Indexer\Model\HandlerInterface;

class AttributeHandler implements HandlerInterface
{
    /**
     * Prepare SQL for field and add it to collection
     *
     * @param SourceProviderInterface $source
     * @param string $alias
     * @param array $fieldInfo
     * @return void
     */
    public function prepareSql(SourceProviderInterface $source, $alias, $fieldInfo)
    {
        if (isset($fieldInfo['bind'])) {
            $source->joinAttribute(
                $fieldInfo['name'],
                $fieldInfo['entity'] . '/' . $fieldInfo['origin'],
                $fieldInfo['bind'],
                null,
                'left'
            );
        } else {
            $source->addAttributeToSelect($fieldInfo['origin'], 'left');
        }
    }
}
