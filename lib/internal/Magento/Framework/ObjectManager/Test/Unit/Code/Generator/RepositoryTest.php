<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Code\Generator;

use Magento\Framework\Api\Test\Unit\Code\Generator\EntityChildTestAbstract;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class RepositoryTest
 */
class RepositoryTest extends EntityChildTestAbstract
{
    protected function getSourceClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample::class;
    }

    protected function getResultClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample\Repository::class;
    }

    protected function getGeneratorClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Repository::class;
    }

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
