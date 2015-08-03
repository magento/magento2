<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\App\Resource\SourceProviderInterface;

interface HandlerInterface
{
    /**
     * @param SourceProviderInterface $source
     * @param array $fieldInfo
     * @return void
     */
    public function prepareSql(SourceProviderInterface $source, $fieldInfo);

    /**
     * @param SourceProviderInterface $source
     * @param array $fieldInfo
     * @return void
     */
    public function prepareData(SourceProviderInterface $source, $fieldInfo);
}
