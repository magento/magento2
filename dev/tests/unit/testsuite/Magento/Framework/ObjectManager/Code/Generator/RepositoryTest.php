<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\ObjectManager\Code\Generator;

use Magento\Framework\Api\Code\Generator\EntityChildTestAbstract;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class RepositoryTest
 */
class RepositoryTest extends EntityChildTestAbstract
{
    const SOURCE_CLASS_NAME = 'Magento\Framework\ObjectManager\Code\Generator\Sample';
    const RESULT_CLASS_NAME = 'Magento\Framework\ObjectManager\Code\Generator\Sample\Repository';
    const GENERATOR_CLASS_NAME = 'Magento\Framework\ObjectManager\Code\Generator\Repository';
    const OUTPUT_FILE_NAME = 'SampleConverter.php';

    protected function getSourceClassName()
    {
        return self::SOURCE_CLASS_NAME;
    }

    protected function getResultClassName()
    {
        return self::RESULT_CLASS_NAME;
    }

    protected function getGeneratorClassName()
    {
        return self::GENERATOR_CLASS_NAME;
    }

    protected function getOutputFileName()
    {
        return self::OUTPUT_FILE_NAME;
    }

    protected function mockDefinedClassesCall()
    {
        $this->definedClassesMock->expects($this->at(0))
            ->method('classLoadable')
            ->with($this->getSourceClassName() . 'Interface')
            ->willReturn(true);
    }
}
