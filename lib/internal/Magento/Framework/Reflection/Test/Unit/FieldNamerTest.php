<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Reflection\FieldNamer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class FieldNamerTest extends TestCase
{
    /**
     * @var FieldNamer
     */
    private $model;

    /**
     * Set up helper.
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(FieldNamer::class);
    }

    /**
     * @param string $methodName
     * @param string $expectedName
     * @dataProvider methodNameProvider
     */
    public function testGetFieldNameForMethodName($methodName, $expectedName)
    {
        $value = $this->model->getFieldNameForMethodName($methodName);
        $this->assertEquals($value, $expectedName);
    }

    /**
     * @return array
     */
    public function methodNameProvider()
    {
        return [
            'isMethod' => ['isValid', 'valid'],
            'getMethod' => ['getValue', 'value'],
            'hasMethod' => ['hasStuff', 'stuff'],
            'randomMethod' => ['randomMethod', null],
        ];
    }
}
