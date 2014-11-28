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

class ResourceConfig extends AbstractSegment
{
    /**
     * Array Key for connection
     */
    const KEY_CONNECTION = 'connection';

    /**
     * Segment key
     */
    const CONFIG_KEY = 'resource';

    /**
     * Constructor
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data = [])
    {
        $this->data = [
            'default_setup' => [
                self::KEY_CONNECTION => 'default',
            ]
        ];
        if (!$this->validate($data)) {
            throw new \InvalidArgumentException('Invalid resource configuration.');
        }
        parent::__construct($this->update($data));
    }

    /**
     * Validate input data
     *
     * @param array $data
     * @return bool
     */
    private function validate(array $data = [])
    {
        foreach ($data as $resource) {
            if (!isset($resource[self::KEY_CONNECTION])) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return self::CONFIG_KEY;
    }
}
