<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\Storage;

use Exception;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Abstract db storage
 */
abstract class AbstractStorage implements StorageInterface
{
    /**
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        protected readonly UrlRewriteFactory $urlRewriteFactory,
        protected readonly DataObjectHelper $dataObjectHelper
    ) {
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
    abstract protected function doFindAllByData(array $data);

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
    abstract protected function doFindOneByData(array $data);

    /**
     * {@inheritdoc}
     */
    public function replace(array $urls)
    {
        if (!$urls) {
            return [];
        }
        return $this->doReplace($urls);
    }

    /**
     * Save new url rewrites and remove old if exist. Template method
     *
     * @param UrlRewrite[] $urls
     * @return UrlRewrite[]
     * @throws UrlAlreadyExistsException|Exception
     */
    abstract protected function doReplace(array $urls);

    /**
     * Create url rewrite object
     *
     * @param array $data
     * @return UrlRewrite
     */
    protected function createUrlRewrite($data)
    {
        $dataObject = $this->urlRewriteFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $dataObject,
            $data,
            UrlRewrite::class
        );
        return $dataObject;
    }
}
