<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Config\Source;

use Magento\Framework\App\State;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class WorkflowType
 *
 * @package Magento\Developer\Model\Config\Source
 */
class WorkflowType implements ArrayInterface
{
    /**
     * Constant for
     */
    const CONFIG_NAME_PATH = 'dev/front_end_development_workflow/type';

    /**
     * Constant for server side compilation workflow
     */
    const SERVER_SIDE_COMPILATION = 'server_side_compilation';

    /**
     * Constant for client side compilation workflow
     */
    const CLIENT_SIDE_COMPILATION = 'client_side_compilation';

    /**
     * Return list of Workflow types
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CLIENT_SIDE_COMPILATION, 'label' => __('Client side less compilation')],
            ['value' => self::SERVER_SIDE_COMPILATION, 'label' => __('Server side less compilation')]
        ];
    }
}
