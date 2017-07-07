<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\Storage;

use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Abstract db storage
 */
abstract class AbstractStorage implements StorageInterface
{
    /** @var UrlRewriteFactory */
    protected $urlRewriteFactory;

    /** @var  DataObjectHelper */
    protected $dataObjectHelper;

    /**
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(UrlRewriteFactory $urlRewriteFactory, DataObjectHelper $dataObjectHelper)
    {
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByData(array $data)
    {
        $rows = $this->doFindAllByData($data);

        $urlRewrites = [];
        foreach ($rows as $row) {
            $urlRewrites[] = $this->createUrlRewrite($row);
        }
        return $urlRewrites;
    }

    /**
     * Find all rows by specific filter. Template method
     *
     * @param array $data
     * @return array
     */
    abstract protected function doFindAllByData($data);

    /**
     * {@inheritdoc}
     */
    public function findOneByData(array $data)
    {
        $row = $this->doFindOneByData($data);

        return $row ? $this->createUrlRewrite($row) : null;
    }

    /**
     * Find row by specific filter. Template method
     *
     * @param array $data
     * @return array
     */
    abstract protected function doFindOneByData($data);

    /**
     * {@inheritdoc}
     */
    public function replace(array $urls)
    {
        if (!$urls) {
            return;
        }

        try {
            $this->doReplace($urls);
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            throw new \Magento\Framework\Exception\AlreadyExistsException(
                __('URL key for specified store already exists.')
            );
        }
    }

    /**
     * Save new url rewrites and remove old if exist. Template method
     *
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $urls
     * @return int
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    abstract protected function doReplace($urls);

    /**
     * Create url rewrite object
     *
     * @param array $data
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite
     */
    protected function createUrlRewrite($data)
    {
        $dataObject = $this->urlRewriteFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $dataObject,
            $data,
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class
        );
        return $dataObject;
    }
}
