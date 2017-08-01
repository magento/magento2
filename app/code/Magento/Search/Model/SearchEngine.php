<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\SearchEngineInterface;

/**
 * Search Engine
 * @since 2.0.0
 */
class SearchEngine implements SearchEngineInterface
{
    /**
     * @var AdapterInterface
     * @since 2.0.0
     */
    private $adapter = null;

    /**
     * Adapter factory
     *
     * @var AdapterFactory
     * @since 2.0.0
     */
    private $adapterFactory;

    /**
     * @param AdapterFactory $adapterFactory
     * @since 2.0.0
     */
    public function __construct(AdapterFactory $adapterFactory)
    {
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function search(RequestInterface $request)
    {
        return $this->getConnection()->query($request);
    }

    /**
     * Get adapter
     *
     * @return AdapterInterface
     * @since 2.0.0
     */
    protected function getConnection()
    {
        if ($this->adapter === null) {
            $this->adapter = $this->adapterFactory->create();
        }
        return $this->adapter;
    }
}
