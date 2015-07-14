<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Handler;

use Magento\Framework\App\Resource\SourceProviderInterface;
use Magento\Indexer\Model\HandlerInterface;

class DefaultHandler implements HandlerInterface
{
    /**
     * @param SourceProviderInterface $source
     * @param array $fieldInfo
     * @return void
     */
    public function prepareSql(SourceProviderInterface $source, $fieldInfo)
    {
        $source->addFieldToSelect(
            isset($fieldInfo['origin']) ? $fieldInfo['origin'] :  $fieldInfo['name'],
            $fieldInfo['name']
        );
    }

    /**
     * @param SourceProviderInterface $source
     * @param array $fieldInfo
     * @return void
     */
    public function prepareData(SourceProviderInterface $source, $fieldInfo)
    {
        new \Exception('Not implemented yet');
    }
}
