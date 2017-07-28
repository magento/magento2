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
 * @since 2.0.0
 */
abstract class AbstractStorage implements StorageInterface
{
    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory
     * @since 2.0.0
     */
    protected $urlRewriteFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     * @since 2.0.0
     */
    protected $dataObjectHelper;

    /**
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param DataObjectHelper $dataObjectHelper
     * @since 2.0.0
     */
    public function __construct(
        UrlRewriteFactory $urlRewriteFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    abstract protected function doFindAllByData(array $data);

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    abstract protected function doFindOneByData(array $data);

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $urls
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     * @throws \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException|\Exception
     * @since 2.0.0
     */
    abstract protected function doReplace(array $urls);

    /**
     * Create url rewrite object
     *
     * @param array $data
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite
     * @since 2.0.0
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
