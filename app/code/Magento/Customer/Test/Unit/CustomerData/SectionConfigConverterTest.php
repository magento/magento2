<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\CustomerData;

use Magento\Framework\App\Arguments\ValidationState;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SectionConfigConverterTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Customer\CustomerData\SectionConfigConverter */
    protected $converter;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \DOMDocument */
    protected $source;

    /** @var \Magento\Framework\Config\Dom config merger */
    private $configMergerClass;

    /**  @var ValidationState */
    private $validationStateMock;

    protected function setUp()
    {
        $this->source = new \DOMDocument();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->converter = $this->objectManagerHelper->getObject(
            \Magento\Customer\CustomerData\SectionConfigConverter::class
        );
        $this->validationStateMock = $this->createMock(ValidationState::class);
    }

    /**
     * Return newly created instance of a config merger
     *
     * @param string $mergerClass
     * @param string $initialContents
     * @return \Magento\Framework\Config\Dom
     * @throws \UnexpectedValueException
     */
    private function createConfig($mergerClass, $initialContents)
    {
        $this->validationStateMock->method('isValidationRequired')->willReturn(\false);
        return new $mergerClass(
            $initialContents,
            $this->validationStateMock,
            [
                '/config/action' => 'name',
                '/config/action/section' => 'name',
            ],
            null,
            null
        );
    }

    public function testConvert()
    {
        $this->source->loadXML(file_get_contents(__DIR__ . '/_files/sections.xml'));

        $this->configMergerClass = $this->createConfig(
            'Magento\Framework\Config\Dom',
            file_get_contents(__DIR__ . '/_files/sections.xml')
        );

        $this->configMergerClass->merge(file_get_contents(__DIR__ . '/_files/sections2.xml'));

        $this->assertEquals(
            [
                'sections' => [
                    'sales/guest/reorder' => ['account'],
                    'sales/order/reorder' => ['account', 'cart'],
                    'stores/store/switch' => ['account', '*', 'cart'],
                    'directory/currency/switch' => ['*'],
                    'customer/account/logout' => ['account', 'cart'],
                    'customer/account/editpost' => ['account', 'acc', 'cart'],
                    'checkout/cart/delete' => ['account', 'acc', 'cart', '*'],
                    'customer/account/createpost' => ['account','*'],
                    'catalog/product_compare/add' => ['*'],
                    'catalog/product_compare/remove' => ['account', 'acc'],
                    'catalog/product_compare/clear' => ['*'],
                    'checkout/cart/add' => ['*'],
                ],
            ],
            $this->converter->convert($this->configMergerClass->getDom())
        );
    }
}
