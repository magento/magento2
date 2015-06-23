<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Fieldset;

use Magento\Indexer\Model\SourcePool;

class DefaultFieldset implements FieldsetInterface
{
    /**
     * @var SourcePool
     */
    private $sourcePool;

    /**
     * @param SourcePool $sourcePool
     */
    public function __construct(SourcePool $sourcePool)
    {
        $this->sourcePool = $sourcePool;
    }
    
    /**
     * {@inheritdoc}
     */
    public function update(array $data)
    {
        return $data;
    }
}
