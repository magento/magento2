<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\App\Cache\Type;

use Magento\Framework\App\DeploymentConfig\AbstractSegment;

/**
 * Deployment configuration segment for enabled cache types
 */
class ConfigSegment extends AbstractSegment
{
    /**
     * Deployment config segment key
     */
    const SEGMENT_KEY = 'cache_types';

    /**
     * Constructor
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (!preg_match('/^[a-z_]+$/i', $key)) {
                throw new \InvalidArgumentException("Invalid cache type key: {$key}");
            }
            $data[$key] = (int)$value;
        }
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return self::SEGMENT_KEY;
    }
}
