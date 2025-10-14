<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @magentoAppArea adminhtml
 */
class TaxTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @dataProvider ajaxActionDataProvider
     * @magentoDbIsolation enabled
     *
     * @param array $postData
     * @param array $expectedData
     */
    public function testAjaxSaveAction($postData, $expectedData)
    {
        $this->getRequest()->setPostValue($postData);

        $this->dispatch('backend/tax/tax/ajaxSave');

        $jsonBody = $this->getResponse()->getBody();
        $result = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Json\Helper\Data::class
        )->jsonDecode(
            $jsonBody
        );

        $this->assertArrayHasKey('class_id', $result);

        $classId = $result['class_id'];
        /** @var $class \Magento\Tax\Model\ClassModel */
        $class = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Tax\Model\ClassModel::class
        )->load($classId, 'class_id');
        $this->assertEquals($expectedData['class_name'], $class->getClassName());
    }

    /**
     * @dataProvider ajaxActionDataProvider
     * @magentoDbIsolation enabled
     *
     * @param array $taxClassData
     */
    public function testAjaxDeleteAction($taxClassData)
    {
        /** @var \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassService */
        $taxClassService = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Tax\Api\TaxClassRepositoryInterface::class
        );

        $taxClassFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Tax\Api\Data\TaxClassInterfaceFactory::class
        );
        $taxClass = $taxClassFactory->create();
        $taxClass->setClassName($taxClassData['class_name'])
            ->setClassType($taxClassData['class_type']);

        $taxClassId = $taxClassService->save($taxClass);

        /** @var $class \Magento\Tax\Model\ClassModel */
        $class = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Tax\Model\ClassModel::class
        )->load($taxClassId, 'class_id');
        $this->assertEquals($taxClassData['class_name'], $class->getClassName());
        $this->assertEquals($taxClassData['class_type'], $class->getClassType());

        $postData = [ 'class_id' => $taxClassId ];
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/tax/tax/ajaxDelete');

        $isFound = true;
        try {
            $taxClassId = $taxClassService->get($taxClassId);
        } catch (NoSuchEntityException $e) {
            $isFound = false;
        }
        $this->assertFalse($isFound, "Tax Class was found when it should have been deleted.");
    }

    /**
     * @return array
     */
    public static function ajaxActionDataProvider()
    {
        return [
            [
                ['class_type' => 'CUSTOMER', 'class_name' => 'Class Name'],
                ['class_name' => 'Class Name'],
            ],
            [
                ['class_type' => 'PRODUCT', 'class_name' => '11111<22222'],
                ['class_name' => '11111<22222']
            ],
            [
                ['class_type' => 'CUSTOMER', 'class_name' => '   12<>sa&df    '],
                ['class_name' => '12<>sa&df']
            ]
        ];
    }
}
