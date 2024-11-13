<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Code\Generator;

use Magento\Framework\Api\Test\Unit\Code\Generator\EntityChildTestAbstract;
use Magento\Framework\ObjectManager\Code\Generator\Repository;

class RepositoryTest extends EntityChildTestAbstract
{
    /**
     * @return string
     */
    protected function getSourceClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample::class;
    }

    /**
     * @return string
     */
    protected function getResultClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample\Repository::class;
    }

    /**
     * @return string
     */
    protected function getGeneratorClassName()
    {
        return '\\' . Repository::class;
    }

    /**
     * @return string
     */
    protected function getOutputFileName()
    {
        return 'SampleConverter.php';
    }

    protected function mockDefinedClassesCall()
    {
        $this->definedClassesMock
            ->method('isClassLoadable')
            ->with($this->getSourceClassName() . 'Interface')
            ->willReturn(true);
    }
}
