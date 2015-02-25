<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Source\Dev;

use Magento\Framework\App\State;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class WorkflowType
 *
 * @package Magento\Backend\Model\Config\Source\Dev
 */
class WorkflowType implements ArrayInterface
{

    /**
     * Constant for server side compilation workflow
     */
    const SERVER_SIDE_COMPILATION = 'server_side_compilation';

    /**
     * Constant for client side compilation workflow
     */
    const CLIENT_SIDE_COMPILATION = 'client_side_compilation';

    /**
     * Constant for advanced compilation workflow
     */
    const ADVANCED_COMPILATION = 'advanced_compilation';

    /**
     * @var string
     */
    private $appMode;

    public static $labels = [
        self::CLIENT_SIDE_COMPILATION => 'Client side less compilation',
        self::SERVER_SIDE_COMPILATION => 'Server side less compilation',
        self::ADVANCED_COMPILATION => 'Advanced less compilation'
    ];
    
    /**
     * @param string $appMode
     */
    function __construct($appMode = State::MODE_DEFAULT)
    {
        $this->appMode = $appMode;
    }

    /**
     * Return list of Workflow types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptionsByAppMode($this->appMode);
    }

    /**
     * Return list of Workflow types by mode
     *
     * @param string $appMode
     *
     * @return array
     */
    private function getOptionsByAppMode($appMode)
    {
        $commonOptions = [
            ['value' => self::SERVER_SIDE_COMPILATION, 'label' => __(self::$labels[self::SERVER_SIDE_COMPILATION])],
        ];
        $options[State::MODE_DEFAULT] = [
            ['value' => self::CLIENT_SIDE_COMPILATION, 'label' => __(self::$labels[self::CLIENT_SIDE_COMPILATION])],
            ['value' => self::ADVANCED_COMPILATION, 'label' => __(self::$labels[self::ADVANCED_COMPILATION])]
        ];
        $options[State::MODE_DEVELOPER] = [
            ['value' => self::CLIENT_SIDE_COMPILATION, 'label' => __(self::$labels[self::CLIENT_SIDE_COMPILATION])],
            ['value' => self::ADVANCED_COMPILATION, 'label' => __(self::$labels[self::ADVANCED_COMPILATION])]
        ];

        return array_merge($commonOptions, $options[$appMode]);
    }
}