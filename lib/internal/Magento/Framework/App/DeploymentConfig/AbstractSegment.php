<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
            } elseif (!isset($value)) {
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
