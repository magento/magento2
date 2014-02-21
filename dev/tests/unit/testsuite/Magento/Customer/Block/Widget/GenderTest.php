<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Block\Widget;

use Magento\Exception\NoSuchEntityException;

class GenderTest extends \PHPUnit_Framework_TestCase
{
    /** Constants used in the unit tests */
    const CUSTOMER_ENTITY_TYPE = 'customer';
    const GENDER_ATTRIBUTE_CODE = 'gender';

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
    private $_abstractAttribute;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\CustomerMetadataServiceInterface
     */
    private $_attributeMetadata;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata */
    private $_attribute;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Session */
    private $_customerSession;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Resource\Customer */
    private $_customerResource;

    /** @var Gender */
    private $_block;

    public function setUp()
    {
        $this->_attribute = $this->getMock('Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata', [], [], '', false);

        $this->_abstractAttribute =
            $this->getMockForAbstractClass(
                'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
                [], '', false, true, true, ['__wakeup', 'getSource']
            );

        $this->_attributeMetadata =
            $this->getMockForAbstractClass(
                'Magento\Customer\Service\V1\CustomerMetadataServiceInterface', [], '', false
            );
        $this->_attributeMetadata->expects($this->any())->method('getAttributeMetadata')
            ->with(self::CUSTOMER_ENTITY_TYPE, self::GENDER_ATTRIBUTE_CODE)
            ->will($this->returnValue($this->_attribute));

        $this->_customerSession = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->_customerResource =
            $this->getMock('Magento\Customer\Model\Resource\Customer', [], [], '', false);

        $this->_block = new Gender(
            $this->getMock('Magento\View\Element\Template\Context', [], [], '', false),
            $this->getMock('Magento\Customer\Helper\Address', [], [], '', false),
            $this->_attributeMetadata,
            $this->_customerSession,
            $this->_customerResource
        );
    }

    /**
     * @param bool $isVisible Determines whether the 'gender' attribute is visible or enabled
     * @param bool $expectedValue The value we expect from Gender::isEnabled()
     * @return void
     *
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($isVisible, $expectedValue)
    {
        $this->_attribute->expects($this->once())->method('isVisible')->will($this->returnValue($isVisible));
        $this->assertSame($expectedValue, $this->_block->isEnabled());
    }

    /**
     * @return array
     */
    public function isEnabledDataProvider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    public function testIsEnabledWithException()
    {
        $this->_attributeMetadata
            ->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->throwException(new NoSuchEntityException('field', 'value')));
        $this->assertSame(false, $this->_block->isEnabled());
    }

    /**
     * @param bool $isRequired Determines whether the 'gender' attribute is required
     * @param bool $expectedValue The value we expect from Gender::isRequired()
     * @return void
     *
     * @dataProvider isRequiredDataProvider
     */
    public function testIsRequired($isRequired, $expectedValue)
    {
        $this->_attribute->expects($this->once())->method('isRequired')->will($this->returnValue($isRequired));
        $this->assertSame($expectedValue, $this->_block->isRequired());
    }

    /**
     * @return array
     */
    public function isRequiredDataProvider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    public function testIsRequiredWithException()
    {
        $this->_attributeMetadata
            ->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->throwException(new NoSuchEntityException('field', 'value')));
        $this->assertSame(false, $this->_block->isRequired());
    }

    public function testGetCustomer()
    {
        /** Do not include prefix, middlename, and suffix attributes when calling Customer::getName() */
        $this->_abstractAttribute->expects($this->any())->method('isVisible')->will($this->returnValue(false));

        $config = $this->getMock('Magento\Eav\Model\Config', [], [], '', false);
        $config->expects($this->any())->method('getAttribute')->will($this->returnValue($this->_abstractAttribute));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $data = ['firstname' => 'John', 'lastname' => 'Doe'];
        $customerModel = $objectManager
            ->getObject('Magento\Customer\Model\Customer', ['config' => $config, 'data' => $data]);
        $this->_customerSession
            ->expects($this->once())->method('getCustomer')->will($this->returnValue($customerModel));

        $customer = $this->_block->getCustomer();
        $this->assertSame($customerModel, $customer);

        $this->assertEquals('John Doe', $customer->getName());
    }

    public function testGetGenderOptions()
    {
        $options = [
            [
                'label' => __('Male'),
                'value' => 'M'
            ],
            [
                'label' => __('Female'),
                'value' => 'F'
            ]
        ];

        $this->_customerResource->expects($this->once())->method('getAttribute')
            ->with(self::GENDER_ATTRIBUTE_CODE)->will($this->returnValue($this->_abstractAttribute));

        $source = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\Attribute\Source\AbstractSource', [], '', false
        );
        $source->expects($this->once())->method('getAllOptions')->will($this->returnValue($options));

        $this->_abstractAttribute->expects($this->once())->method('getSource')->will($this->returnValue($source));

        $this->assertSame($options, $this->_block->getGenderOptions());
    }
}
