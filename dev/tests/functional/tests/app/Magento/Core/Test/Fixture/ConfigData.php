<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Core\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class ConfigData
 * Config fixture
 */
class ConfigData extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Core\Test\Repository\ConfigData';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Core\Test\Handler\ConfigData\ConfigDataInterface';

    /**
     * @var array
     */
    protected $section = [
        'attribute_code' => 'section',
        'backend_type' => 'virtual',
    ];

    protected $config_id = [
        'attribute_code' => 'config_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $scope = [
        'attribute_code' => 'scope',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => 'default',
        'input' => '',
    ];

    protected $scope_id = [
        'attribute_code' => 'scope_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $path = [
        'attribute_code' => 'path',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => 'general',
        'input' => '',
    ];

    protected $value = [
        'attribute_code' => 'value',
        'backend_type' => 'text',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    public function getSection()
    {
        return $this->getData('section');
    }

    public function getConfigId()
    {
        return $this->getData('config_id');
    }

    public function getScope()
    {
        return $this->getData('scope');
    }

    public function getScopeId()
    {
        return $this->getData('scope_id');
    }

    public function getPath()
    {
        return $this->getData('path');
    }

    public function getValue()
    {
        return $this->getData('value');
    }
}
