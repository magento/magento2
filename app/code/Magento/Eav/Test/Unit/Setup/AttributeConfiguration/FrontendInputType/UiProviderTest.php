<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\AttributeConfiguration\FrontendInputType;

use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator;
use Magento\Eav\Setup\AttributeConfiguration\FrontendInputType\UiProvider;

class UiProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var UiProvider
     */
    private $provider;

    protected function setUp()
    {
        $this->validator = $this->getMockBuilder(Validator::class)
                                ->setMethods(['isValid'])
                                ->disableOriginalConstructor()
                                ->getMock();

        $this->provider = new UiProvider($this->validator);
    }

    public function testProviderReturnsTrueOnValidInputExistenceCheck()
    {
        $this->validator->expects($this->once())
                        ->method('isValid')
                        ->with('frontend_input_text')
                        ->willReturn(true);

        $this->assertTrue($this->provider->exists('frontend_input_text'));
    }

    public function testProviderReturnsFalseOnValidInputExistenceCheck()
    {
        $this->validator->expects($this->once())
                        ->method('isValid')
                        ->with('invalid_frontend_input_text')
                        ->willReturn(false);

        $this->assertFalse($this->provider->exists('invalid_frontend_input_text'));
    }

    /**
     * @expectedException \Magento\Eav\Setup\AttributeConfiguration\InvalidConfigurationException
     */
    public function testProviderThrowsOnInvalidInputTypeWhenDirectlyNormalizing()
    {
        $this->validator->expects($this->once())
                        ->method('isValid')
                        ->with('frontend_input_text')
                        ->willReturn(false);

        $this->provider->normalize('frontend_input_text');
    }

    public function testProviderDoesNotThrowOnValidInputTypeWhenDirectlyNormalizing()
    {
        $this->validator->expects($this->once())
                        ->method('isValid')
                        ->with('frontend_input_text')
                        ->willReturn(true);

        try {
            $this->provider->normalize('frontend_input_text');
        } catch (\Exception $e) {
            $this->fail('Normalizing should not throw on valid input type');
        }
    }
}
