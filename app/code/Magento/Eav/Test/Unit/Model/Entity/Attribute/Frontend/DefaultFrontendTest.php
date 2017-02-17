<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Frontend;

use Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend;

class DefaultFrontendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultFrontend
     */
    protected $model;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $booleanFactory;

    protected function setUp()
    {
        $this->booleanFactory = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new DefaultFrontend(
            $this->booleanFactory
        );
    }

    public function testGetClassEmpty()
    {
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIsRequired',
                'getFrontendClass',
                'getValidateRules',
            ])
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getIsRequired')
            ->willReturn(false);
        $attributeMock->expects($this->once())
            ->method('getFrontendClass')
            ->willReturn('');
        $attributeMock->expects($this->exactly(2))
            ->method('getValidateRules')
            ->willReturn('');

        $this->model->setAttribute($attributeMock);
        $this->assertEmpty($this->model->getClass());
    }

    public function testGetClass()
    {
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIsRequired',
                'getFrontendClass',
                'getValidateRules',
            ])
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getIsRequired')
            ->willReturn(true);
        $attributeMock->expects($this->once())
            ->method('getFrontendClass')
            ->willReturn('');
        $attributeMock->expects($this->exactly(3))
            ->method('getValidateRules')
            ->willReturn([
                'input_validation' => 'alphanumeric',
                'min_text_length' => 1,
                'max_text_length' => 2,
            ]);

        $this->model->setAttribute($attributeMock);
        $result = $this->model->getClass();

        $this->assertContains('validate-alphanum', $result);
        $this->assertContains('minimum-length-1', $result);
        $this->assertContains('maximum-length-2', $result);
        $this->assertContains('validate-length', $result);
    }

    public function testGetClassLength()
    {
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIsRequired',
                'getFrontendClass',
                'getValidateRules',
            ])
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getIsRequired')
            ->willReturn(true);
        $attributeMock->expects($this->once())
            ->method('getFrontendClass')
            ->willReturn('');
        $attributeMock->expects($this->exactly(3))
            ->method('getValidateRules')
            ->willReturn([
                'input_validation' => 'length',
                'min_text_length' => 1,
                'max_text_length' => 2,
            ]);

        $this->model->setAttribute($attributeMock);
        $result = $this->model->getClass();

        $this->assertContains('minimum-length-1', $result);
        $this->assertContains('maximum-length-2', $result);
        $this->assertContains('validate-length', $result);
    }
}
