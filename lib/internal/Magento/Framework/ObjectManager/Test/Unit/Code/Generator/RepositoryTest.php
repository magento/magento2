<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Code\Generator;

use Magento\Framework\Api\Test\Unit\Code\Generator\EntityChildTestAbstract;

/**
 * Class RepositoryTest
 */
class RepositoryTest extends EntityChildTestAbstract
{
    /**
     * @return mixed|string
     */
    protected function getSourceClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample::class;
    }

    /**
     * @return mixed|string
     */
    protected function getResultClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample\Repository::class;
    }

    /**
     * @return mixed|string
     */
    protected function getGeneratorClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Repository::class;
    }

    /**
     * @return mixed|string
     */
    protected function getOutputFileName()
    {
        return 'SampleConverter.php';
    }

    protected function mockDefinedClassesCall()
    {
        $this->definedClassesMock->expects($this->at(0))
            ->method('isClassLoadable')
            ->with($this->getSourceClassName() . 'Interface')
            ->willReturn(true);
    }
}
