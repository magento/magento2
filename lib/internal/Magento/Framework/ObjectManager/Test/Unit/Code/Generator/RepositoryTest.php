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
<<<<<<< HEAD
     * @return string
=======
     * @return mixed|string
>>>>>>> upstream/2.2-develop
     */
    protected function getSourceClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample::class;
    }

    /**
<<<<<<< HEAD
     * @return string
=======
     * @return mixed|string
>>>>>>> upstream/2.2-develop
     */
    protected function getResultClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample\Repository::class;
    }

    /**
<<<<<<< HEAD
     * @return string
=======
     * @return mixed|string
>>>>>>> upstream/2.2-develop
     */
    protected function getGeneratorClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Repository::class;
    }

    /**
<<<<<<< HEAD
     * @return string
=======
     * @return mixed|string
>>>>>>> upstream/2.2-develop
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
