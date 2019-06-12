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
     * @return mixed|string
=======
     * @return string
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    protected function getSourceClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample::class;
    }

    /**
<<<<<<< HEAD
     * @return mixed|string
=======
     * @return string
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    protected function getResultClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample\Repository::class;
    }

    /**
<<<<<<< HEAD
     * @return mixed|string
=======
     * @return string
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    protected function getGeneratorClassName()
    {
        return '\\' . \Magento\Framework\ObjectManager\Code\Generator\Repository::class;
    }

    /**
<<<<<<< HEAD
     * @return mixed|string
=======
     * @return string
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
