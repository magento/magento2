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
namespace Magento\Framework\Search;

use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\QueryInterface;

/**
 * Search Request
 *
 * @codeCoverageIgnore
 */
class Request implements RequestInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $index;

    /**
     * @var BucketInterface[]
     */
    protected $buckets;

    /**
     * Main query which represents the whole query hierarchy
     *
     * @var QueryInterface
     */
    protected $query;

    /**
     * @var int|null
     */
    protected $from;

    /**
     * @var int|null
     */
    protected $size;

    /**
     * @var Dimension[]
     */
    protected $dimensions;

    /**
     * @param string $name
     * @param string $indexName
     * @param QueryInterface $query
     * @param int|null $from
     * @param int|null $size
     * @param Dimension[] $dimensions
     * @param BucketInterface[] $buckets
     */
    public function __construct(
        $name,
        $indexName,
        QueryInterface $query,
        $from = null,
        $size = null,
        array $dimensions = null,
        array $buckets = null
    ) {
        $this->name = $name;
        $this->index = $indexName;
        $this->query = $query;
        $this->from = $from;
        $this->size = $size;
        $this->buckets = $buckets;
        $this->dimensions = $dimensions;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return Dimension[]
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregation()
    {
        return $this->buckets;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }
}
