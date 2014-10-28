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
namespace Magento\Tax\Controller\Adminhtml;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @magentoAppArea adminhtml
 */
class TaxTest extends \Magento\Backend\Utility\Controller
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
        $this->getRequest()->setPost($postData);

        $this->dispatch('backend/tax/tax/ajaxSave');

        $jsonBody = $this->getResponse()->getBody();
        $result = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Core\Helper\Data'
        )->jsonDecode(
            $jsonBody
        );

        $this->assertArrayHasKey('class_id', $result);

        $classId = $result['class_id'];
        /** @var $rate \Magento\Tax\Model\ClassModel */
        $class = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Tax\Model\ClassModel')
            ->load($classId, 'class_id');
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
        $taxClassService = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Tax\Service\V1\TaxClassServiceInterface'
        );

        $taxClassBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Tax\Service\V1\Data\TaxClassBuilder'
        );

        $taxClass = $taxClassBuilder->setClassName($taxClassData['class_name'])
            ->setClassType($taxClassData['class_type'])
            ->create();

        $taxClassId = $taxClassService->createTaxClass($taxClass);

        /** @var $rate \Magento\Tax\Model\ClassModel */
        $class = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Tax\Model\ClassModel')
            ->load($taxClassId, 'class_id');
        $this->assertEquals($taxClassData['class_name'], $class->getClassName());
        $this->assertEquals($taxClassData['class_type'], $class->getClassType());

        $postData = [ 'class_id' => $taxClassId ];
        $this->getRequest()->setPost($postData);
        $this->dispatch('backend/tax/tax/ajaxDelete');

        $isFound = true;
        try {
            $taxClassId = $taxClassService->getTaxClass($taxClassId);
        } catch (NoSuchEntityException $e) {
            $isFound = false;
        }
        $this->assertFalse($isFound, "Tax Class was found when it should have been deleted.");
    }

    /**
     * @return array
     */
    public function ajaxActionDataProvider()
    {
        return array(
            array(
                array('class_type' => 'CUSTOMER', 'class_name' => 'Class Name'),
                array('class_name' => 'Class Name')
            ),
            array(
                array('class_type' => 'PRODUCT', 'class_name' => '11111<22222'),
                array('class_name' => '11111&lt;22222')
            ),
            array(
                array('class_type' => 'CUSTOMER', 'class_name' => '   12<>sa&df    '),
                array('class_name' => '12&lt;&gt;sa&amp;df')
            )
        );
    }
}
