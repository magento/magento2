<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\UrlRewrite\Model\Storage;

use Magento\Framework\App\Resource;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder;

/**
 * Abstract db storage
 */
abstract class AbstractStorage implements StorageInterface
{
    /** @var UrlRewriteBuilder */
    protected $urlRewriteBuilder;

    /**
     * @param UrlRewriteBuilder $urlRewriteBuilder
     */
    public function __construct(UrlRewriteBuilder $urlRewriteBuilder)
    {
        $this->urlRewriteBuilder = $urlRewriteBuilder;
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
        } catch (DuplicateEntryException $e) {
            throw new DuplicateEntryException(__('URL key for specified store already exists.'));
        }
    }

    /**
     * Save new url rewrites and remove old if exist. Template method
     *
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $urls
     * @return int
     * @throws DuplicateEntryException
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
        return $this->urlRewriteBuilder->populateWithArray($data)->create();
    }
}
