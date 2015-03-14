<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogImportExport\Model\Import\Product\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Validator */
    protected $validator;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var array */
    protected $validators = [];

    /** @var Validator\Media|\PHPUnit_Framework_MockObject_MockObject */
    protected $validator1;

    /** @var Validator\Category|\PHPUnit_Framework_MockObject_MockObject */
    protected $validator2;

    protected function setUp()
    {
        $this->validator1 = $this->getMock(
            'Magento\CatalogImportExport\Model\Import\Product\Validator\Media',
            [],
            [],
            '',
            false
        );
        $this->validator2 = $this->getMock(
            'Magento\CatalogImportExport\Model\Import\Product\Validator\Category',
            [],
            [],
            '',
            false
        );

        $this->validators = [$this->validator1, $this->validator2];
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->validator = $this->objectManagerHelper->getObject(
            'Magento\CatalogImportExport\Model\Import\Product\Validator',
            ['validators' => $this->validators]
        );
    }

    public function testIsValidCorrect()
    {
        $value = 'val';
        $this->validator1->expects($this->once())->method('isValid')->with($value)->willReturn(true);
        $this->validator2->expects($this->once())->method('isValid')->with($value)->willReturn(true);
        $result = $this->validator->isValid($value);
        $this->assertTrue($result);
    }

    public function testIsValidIncorrect()
    {
        $value = 'val';
        $this->validator1->expects($this->once())->method('isValid')->with($value)->willReturn(true);
        $this->validator2->expects($this->once())->method('isValid')->with($value)->willReturn(false);
        $messages = ['errorMessage'];
        $this->validator2->expects($this->once())->method('getMessages')->willReturn($messages);
        $result = $this->validator->isValid($value);
        $this->assertFalse($result);
        $this->assertEquals($messages, $this->validator->getMessages());
    }

    public function testInit()
    {
        $this->validator1->expects($this->once())->method('init');
        $this->validator2->expects($this->once())->method('init');
        $this->validator->init();
    }
}
