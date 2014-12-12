<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\App\DeploymentConfig;

class SessionConfig extends AbstractSegment
{
    /**
     * Array Key for session save method
     */
    const KEY_SAVE = 'save';

    /**
     * Segment key
     */
    const CONFIG_KEY = 'session';

    /**
     * Constructor
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data)
    {
        if (!isset($data[self::KEY_SAVE])) {
            $data = [
                self::KEY_SAVE => 'files',
            ];
        } elseif ($data[self::KEY_SAVE] !== 'files' && $data[self::KEY_SAVE] !== 'db') {
            throw new \InvalidArgumentException("Invalid session_save location {$data[self::KEY_SAVE]}");
        }

        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return self::CONFIG_KEY;
    }
}
