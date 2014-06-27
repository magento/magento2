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

namespace Magento\Tax\Service\V1;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Tax\Service\V1\Data\TaxRule;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;

/**
 * Class TaxRuleServiceTest tests Magento/Tax/Service/V1/TaxRuleService
 *
 */
class TaxRuleServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * TaxRule builder
     *
     * @var \Magento\Tax\Service\V1\Data\TaxRuleBuilder
     */
    private $taxRuleBuilder;

    /**
     * TaxRuleService
     *
     * @var \Magento\Tax\Service\V1\TaxRuleServiceInterface
     */
    private $taxRuleService;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->taxRuleService = $this->objectManager->get('Magento\Tax\Service\V1\TaxRuleServiceInterface');
        $this->taxRuleBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateTaxRule()
    {
        // Tax rule data object created
        $taxRuleDataObject = $this->createTaxRuleDataObject();
        //Tax rule service call
        $taxRuleServiceData = $this->taxRuleService->createTaxRule($taxRuleDataObject);

        //Assertions
        $this->assertInstanceOf('\Magento\Tax\Service\V1\Data\TaxRule', $taxRuleServiceData);
        $this->assertEquals($taxRuleDataObject->getCode(), $taxRuleServiceData->getCode());
        $this->assertEquals(
            $taxRuleDataObject->getCustomerTaxClassIds(),
            $taxRuleServiceData->getCustomerTaxClassIds()
        );
        $this->assertEquals($taxRuleDataObject->getProductTaxClassIds(), $taxRuleServiceData->getProductTaxClassIds());
        $this->assertEquals($taxRuleDataObject->getPriority(), $taxRuleServiceData->getPriority());
        $this->assertEquals($taxRuleDataObject->getSortOrder(), $taxRuleServiceData->getSortOrder());
        $this->assertNotNull($taxRuleServiceData->getId());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateTaxRuleInvalid()
    {
        $taxRuleData = [
            TaxRule::CODE => 'code',
            TaxRule::CUSTOMER_TAX_CLASS_IDS => [3],
            TaxRule::PRODUCT_TAX_CLASS_IDS => [2],
            TaxRule::TAX_RATE_IDS => [1],
            TaxRule::PRIORITY => 0,
            TaxRule::SORT_ORDER => -1,
        ];
        // Tax rule data object created
        $taxRule = $this->taxRuleBuilder->populateWithArray($taxRuleData)->create();

        try {
            //Tax rule service call
            $this->taxRuleService->createTaxRule($taxRule);
            $this->fail('Did not throw expected InputException');
        } catch (InputException $e) {
            $expectedParams = [
                'fieldName' => taxRule::SORT_ORDER,
                'value' => -1,
                'minValue' => '0',
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
            $this->assertEquals(InputException::INVALID_FIELD_MIN_VALUE, $e->getRawMessage());
        }
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetTaxRuleCreatedFromService()
    {
        // Tax rule data object created
        $taxRuleDataObject = $this->createTaxRuleDataObject();
        //Tax rule service call to create rule
        $ruleId = $this->taxRuleService->createTaxRule($taxRuleDataObject)->getId();

        // Call getTaxRule and verify
        $taxRule = $this->taxRuleService->getTaxRule($ruleId);
        $this->assertEquals('code', $taxRule->getCode());
        $this->assertEquals([3], $taxRule->getCustomerTaxClassIds());
        $this->assertEquals([2], $taxRule->getProductTaxClassIds());
        $this->assertEquals([2], $taxRule->getTaxRateIds());
        $this->assertEquals(0, $taxRule->getPriority());
        $this->assertEquals(1, $taxRule->getSortOrder());
    }
    /**
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testGetTaxRuleCreatedFromModel()
    {
        /** @var $registry \Magento\Framework\Registry */
        $registry = $this->objectManager->get('Magento\Framework\Registry');
        /** @var $taxRuleModel \Magento\Tax\Model\Calculation\Rule */
        $taxRuleModel = $registry->registry('_fixture/Magento_Tax_Model_Calculation_Rule');
        $this->assertNotNull($taxRuleModel);
        $ruleId = $taxRuleModel->getId();

        $taxRateId = $registry->registry('_fixture/Magento_Tax_Model_Calculation_Rate')->getId();
        $customerTaxClassIds = array_unique($taxRuleModel->getCustomerTaxClasses());

        // Call getTaxRule and verify
        $taxRule = $this->taxRuleService->getTaxRule($ruleId);
        $this->assertEquals($customerTaxClassIds, $taxRule->getCustomerTaxClassIds());
        $this->assertEquals([$taxRateId], $taxRule->getTaxRateIds());
    }

    /**
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testDeleteTaxRule()
    {
        /** @var $registry \Magento\Framework\Registry */
        $registry = $this->objectManager->get('Magento\Framework\Registry');
        /** @var $taxRule \Magento\Tax\Model\Calculation\Rule */
        $taxRule = $registry->registry('_fixture/Magento_Tax_Model_Calculation_Rule');
        $this->assertNotNull($taxRule);
        $ruleId = $taxRule->getId();

        // Delete the new tax rate
        $this->assertTrue($this->taxRuleService->deleteTaxRule($ruleId));

        // Get the new tax rule, this should fail
        try {
            $this->taxRuleService->getTaxRule($ruleId);
            $this->fail('NoSuchEntityException expected but not thrown');
        } catch (NoSuchEntityException $e) {
            $expectedParams = [
                'fieldName' => 'taxRuleId',
                'fieldValue' => $ruleId,
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
        }
    }

    /**
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testDeleteTaxRateException()
    {
        /** @var $registry \Magento\Framework\Registry */
        $registry = $this->objectManager->get('Magento\Framework\Registry');
        /** @var $taxRule \Magento\Tax\Model\Calculation\Rule */
        $taxRule = $registry->registry('_fixture/Magento_Tax_Model_Calculation_Rule');
        $this->assertNotNull($taxRule);
        $ruleId = $taxRule->getId();

        // Delete the new tax rate
        $this->assertTrue($this->taxRuleService->deleteTaxRule($ruleId));

        // Delete the new tax rate again, this should fail
        try {
            $this->taxRuleService->deleteTaxRule($ruleId);
            $this->fail('NoSuchEntityException expected but not thrown');
        } catch (NoSuchEntityException $e) {
            $expectedParams = [
                'fieldName' => 'taxRuleId',
                'fieldValue' => $ruleId,
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
        }
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testUpdateTaxRule()
    {

        $taxRule = $this->createTaxRuleDataObject();
        //Tax rule service call
        $taxRuleServiceData = $this->taxRuleService->createTaxRule($taxRule);

        $updatedTaxRule = $this->taxRuleBuilder->populate($taxRuleServiceData)
            ->setCode('updated code')
            ->create();

        $this->taxRuleService->updateTaxRule($updatedTaxRule);
        $retrievedRule = $this->taxRuleService->getTaxRule($taxRuleServiceData->getId());

        //Assertion
        $this->assertEquals($updatedTaxRule->__toArray(), $retrievedRule->__toArray());
        $this->assertNotEquals($taxRule->__toArray(), $retrievedRule->__toArray());
    }

    /**
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage taxRuleId =
     */
    public function testUpdateTaxRuleNoId()
    {
        $this->taxRuleService->updateTaxRule($this->createTaxRuleDataObject());
    }

    /**
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage code
     */
    public function testUpdateTaxRuleMissingRequiredFields()
    {
        $taxRuleServiceData = $this->taxRuleService->createTaxRule($this->createTaxRuleDataObject());
        $updatedTaxRule = $this->taxRuleBuilder->populate($taxRuleServiceData)
            ->setCode(null)
            ->create();

        $this->taxRuleService->updateTaxRule($updatedTaxRule);
    }

    /**
     * Creates Tax Rule Data Object
     *
     * @return \Magento\Tax\Service\V1\Data\TaxRule
     */
    private function createTaxRuleDataObject()
    {
        return $this->taxRuleBuilder
            ->setCode('code')
            ->setCustomerTaxClassIds([3])
            ->setProductTaxClassIds([2])
            ->setTaxRateIds([2])
            ->setPriority(0)
            ->setSortOrder(1)
            ->create();
    }
}
