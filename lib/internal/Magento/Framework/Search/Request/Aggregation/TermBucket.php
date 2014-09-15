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
namespace Magento\Framework\Search\Request\Aggregation;

use Magento\Framework\Search\Request\BucketInterface;

/**
 * Term Buckets
 */
class TermBucket implements BucketInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var array
     */
    protected $metrics;

    /**
     * @param string $name
     * @param string $field
     * @param array $metrics
     */
    public function __construct($name, $field, array $metrics)
    {
        $this->name = $name;
        $this->field = $field;
        $this->metrics = $metrics;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return BucketInterface::TYPE_TERM;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Get Metrics
     *
     * @return array
     */
    public function getMetrics()
    {
        return $this->metrics;
    }
}
