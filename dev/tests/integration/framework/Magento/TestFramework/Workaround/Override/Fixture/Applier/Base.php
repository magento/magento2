<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override\Fixture\Applier;

/**
 * Class determine base appliers logic
 */
abstract class Base implements ApplierInterface
{
    /** @var array */
    private $classConfig;

    /** @var array */
    private $methodConfig;

    /** @var array */
    private $dataSetConfig;

    /**
     * Get class node config
     *
     * @return array
     */
    public function getClassConfig(): array
    {
        return $this->classConfig;
    }

    /**
     * Set class node config
     *
     * @param array $classConfig
     * @return void
     */
    public function setClassConfig(array $classConfig): void
    {
        $this->classConfig = $classConfig;
    }

    /**
     * Get method node config
     *
     * @return array
     */
    public function getMethodConfig(): array
    {
        return $this->methodConfig;
    }

    /**
     * Set method node config
     *
     * @param array $methodConfig
     * @return void
     */
    public function setMethodConfig(array $methodConfig): void
    {
        $this->methodConfig = $methodConfig;
    }

    /**
     * Get data set node config
     *
     * @return array
     */
    public function getDataSetConfig(): array
    {
        return $this->dataSetConfig;
    }

    /**
     * Set data set node config
     *
     * @param array $dataSetConfig
     * @return void
     */
    public function setDataSetConfig(array $dataSetConfig): void
    {
        $this->dataSetConfig = $dataSetConfig;
    }

    /**
     * Need set config exact in such order according to priority level
     *
     * @return array
     */
    protected function getPrioritizedConfig(): array
    {
        return [
            $this->getClassConfig(),
            $this->getMethodConfig(),
            $this->getDataSetConfig(),
        ];
    }
}
