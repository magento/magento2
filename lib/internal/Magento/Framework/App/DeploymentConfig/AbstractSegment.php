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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App\DeploymentConfig;

abstract class AbstractSegment implements SegmentInterface
{
    /**
     * Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Update data
     *
     * @param string[] $data
     * @return array
     */
    protected function update(array $data)
    {
        // get rid of null values
        $data = $this->filterArray($data);
        if (empty($data)) {
            return $this->data;
        }

        $new = array_replace_recursive($this->data, $data);
        return $new;
    }

    /**
     * Filter an array recursively
     *
     * @param array $data
     * @return array
     */
    private function filterArray(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->filterArray($value);
            } else if (!isset($value)) {
                unset($data[$key]);
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getKey();

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }
}
