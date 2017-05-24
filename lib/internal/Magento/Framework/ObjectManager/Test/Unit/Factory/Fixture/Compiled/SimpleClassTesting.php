<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Compiled;

class SimpleClassTesting
{
    /**
     * @var DependencyTesting
     */
    private $nonSharedDependency;

    /**
     * @var DependencySharedTesting
     */
    private $sharedDependency;

    /**
     * @var string
     */
    private $value;

    /**
     * @var array
     */
    private $valueArray;

    /**
     * @var string
     */
    private $globalValue;

    /**
     * @var
     */
    private $nullValue;

    /**
     * @param DependencyTesting $nonSharedDependency
     * @param DependencySharedTesting $sharedDependency
     * @param string $value
     * @param array $valueArray
     * @param string $globalValue
     * @param null $nullValue
     */
    public function __construct(
        DependencyTesting $nonSharedDependency,
        DependencySharedTesting $sharedDependency,
        $value = 'value',
        array $valueArray = [
        'default_value1',
        'default_value2'
        ],
        $globalValue = '',
        $nullValue = null
    ) {

        $this->nonSharedDependency = $nonSharedDependency;
        $this->sharedDependency = $sharedDependency;
        $this->value = $value;
        $this->valueArray = $valueArray;
        $this->globalValue = $globalValue;
        $this->nullValue = $nullValue;
    }

    /**
     * @return mixed
     */
    public function getNullValue()
    {
        return $this->nullValue;
    }

    /**
     * @return string
     */
    public function getGlobalValue()
    {
        return $this->globalValue;
    }

    /**
     * @return DependencyTesting
     */
    public function getNonSharedDependency()
    {
        return $this->nonSharedDependency;
    }

    /**
     * @return DependencySharedTesting
     */
    public function getSharedDependency()
    {
        return $this->sharedDependency;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function getValueArray()
    {
        return $this->valueArray;
    }
}
