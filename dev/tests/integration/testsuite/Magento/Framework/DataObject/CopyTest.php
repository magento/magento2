<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject;

class CopyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $_service;

    protected function setUp()
    {
        $this->_service = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\DataObject\Copy::class);
    }

    public function testCopyFieldset()
    {
        $fieldset = 'sales_copy_order';
        $aspect = 'to_edit';
        $data = ['customer_email' => 'admin@example.com', 'customer_group_id' => '1'];
        $source = new \Magento\Framework\DataObject($data);
        $target = new \Magento\Framework\DataObject();
        $expectedTarget = new \Magento\Framework\DataObject($data);

        $this->assertNull($this->_service->copyFieldsetToTarget($fieldset, $aspect, 'invalid_source', []));
        $this->assertNull($this->_service->copyFieldsetToTarget($fieldset, $aspect, [], 'invalid_target'));
        $this->assertEquals(
            $target,
            $this->_service->copyFieldsetToTarget('invalid_fieldset', $aspect, $source, $target)
        );
        $this->assertSame($target, $this->_service->copyFieldsetToTarget($fieldset, $aspect, $source, $target));
        $this->assertEquals($expectedTarget, $target);
    }

    public function testCopyFieldsetWithExtensionAttributes()
    {
        $autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
        $autoloadWrapper->addPsr4('Magento\\Wonderland\\', realpath(__DIR__ . '/_files/Magento/Wonderland'));
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $fieldsetConfigMock = $this->getMockBuilder(\Magento\Framework\DataObject\Copy\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldSet'])
            ->getMock();

        $service = $objectManager->create(
            \Magento\Framework\DataObject\Copy::class,
            ['fieldsetConfig' => $fieldsetConfigMock]
        );

        $data = ['firstname' => ['name' => '*'], 'lastname' => ['name' => '*'], 'test_group_code' => ['name' => '*']];
        $fieldsetConfigMock
            ->expects($this->once())
            ->method('getFieldSet')
            ->willReturn($data);

        $fieldset = 'customer_account';
        $aspect = 'name';
        $groupCode = 'general';
        $firstName = 'First';
        $data = [
            'email'                => 'customer@example.com',
            'firstname'            => $firstName,
            'lastname'             => 'Last',
            // see declaration in dev/tests/integration/testsuite/Magento/Framework/Api/etc/extension_attributes.xml
            'extension_attributes' => ['test_group_code' => $groupCode]
        ];
        $dataWithExtraField = array_merge($data, ['undeclared_field' => 'will be omitted']);

        /** @var \Magento\Framework\Api\DataObjectHelper $dataObjectHelper */
        $dataObjectHelper = $objectManager->get(\Magento\Framework\Api\DataObjectHelper::class);
        /** @var \Magento\Wonderland\Model\Data\FakeCustomerFactory $customerFactory */
        $customerFactory = $objectManager->get(\Magento\Wonderland\Model\Data\FakeCustomerFactory::class);
        /** @var \Magento\Wonderland\Api\Data\CustomerInterface $source */
        $source = $customerFactory->create();
        $dataObjectHelper->populateWithArray(
            $source,
            $dataWithExtraField,
            \Magento\Wonderland\Api\Data\FakeCustomerInterface::class
        );
        /** @var \Magento\Wonderland\Api\Data\CustomerInterface $target */
        $target = $customerFactory->create();
        $target = $service->copyFieldsetToTarget($fieldset, $aspect, $source, $target);

        $this->assertInstanceOf(\Magento\Wonderland\Api\Data\FakeCustomerInterface::class, $target);
        $this->assertNull(
            $target->getEmail(),
            "Email should not be set because it is not defined in the fieldset."
        );
        $this->assertEquals(
            $firstName,
            $target->getFirstname(),
            "First name was not copied."
        );
        $this->assertEquals(
            $groupCode,
            $target->getExtensionAttributes()->getTestGroupCode(),
            "Extension attribute was not copied."
        );
    }

    public function testCopyFieldsetWithAbstractSimpleObject()
    {
        $autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
        $autoloadWrapper->addPsr4('Magento\\Wonderland\\', realpath(__DIR__ . '/_files/Magento/Wonderland'));
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $fieldset = 'sales_copy_order';
        $aspect = 'to_edit';

        $fieldsetConfigMock = $this->getMockBuilder(\Magento\Framework\DataObject\Copy\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldSet'])
            ->getMock();

        $service = $objectManager->create(
            \Magento\Framework\DataObject\Copy::class,
            ['fieldsetConfig' => $fieldsetConfigMock]
        );

        $data = ['store_label' => ['to_edit' => '*'], 'frontend_label' => ['to_edit' => '*'],
                 'attribute_code' => ['to_edit' => '*'], 'note' => ['to_edit' => '*']];
        $fieldsetConfigMock
            ->expects($this->any())
            ->method('getFieldSet')
            ->willReturn($data);

        $source = $objectManager->get(\Magento\Wonderland\Model\Data\FakeAttributeMetadata::class);
        $source->setStoreLabel('storeLabel');
        $source->setFrontendLabel('frontendLabel');
        $source->setAttributeCode('attributeCode');
        $source->setNote('note');

        $target = $objectManager->get(\Magento\Wonderland\Model\Data\FakeAttributeMetadata::class);
        $expectedTarget = $source;

        $this->assertEquals(
            $target,
            $service->copyFieldsetToTarget('invalid_fieldset', $aspect, $source, $target)
        );
        $this->assertEquals(
            $expectedTarget,
            $service->copyFieldsetToTarget($fieldset, $aspect, $source, $target)
        );
    }

    public function testCopyFieldsetArrayTarget()
    {
        $fieldset = 'sales_copy_order';
        $aspect = 'to_edit';
        $data = ['customer_email' => 'admin@example.com', 'customer_group_id' => '1'];
        $source = new \Magento\Framework\DataObject($data);
        $target = [];
        $expectedTarget = $data;

        $this->assertEquals(
            $target,
            $this->_service->copyFieldsetToTarget('invalid_fieldset', $aspect, $source, $target)
        );
        $this->assertEquals(
            $expectedTarget,
            $this->_service->copyFieldsetToTarget($fieldset, $aspect, $source, $target)
        );
    }
}
