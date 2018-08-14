<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Plugin\Model\Indexer\Category\Product;

use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;

class Execute
{
    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $typeList;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $typeList
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\Framework\App\Cache\TypeListInterface $typeList
    ) {
        $this->config = $config;
        $this->typeList = $typeList;
    }

    /**
     * @param AbstractAction $subject
     * @param AbstractAction $result
     * @return AbstractAction
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(AbstractAction $subject, AbstractAction $result)
    {
        if ($this->config->isEnabled()) {
            $this->typeList->invalidate('full_page');
        }
        return $result;
    }
}
