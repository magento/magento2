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
namespace Magento\Customer\Service\V1\Data\Eav;

use Magento\Customer\Service\V1\Data\Eav\AttributeMetadata;
use Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder;

class AttributeMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Constants for testing
     */
    const ATTRIBUTE_CODE = 'ATTRIBUTE_CODE';

    const FRONTEND_INPUT = 'FRONT_END_INPUT';

    const INPUT_FILTER = 'INPUT_FILTER';

    const STORE_LABEL = 'STORE_LABEL';

    const VALIDATION_RULES = 'VALIDATION_RULES';

    public function testConstructorAndGetters()
    {
        $options = array(array('value' => 'OPTION_ONE'), array('value' => 'OPTION_TWO'));
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Customer\Service\V1\Data\Eav\OptionBuilder $optionBuilder */
        $optionBuilder = $objectManager->getObject('Magento\Customer\Service\V1\Data\Eav\OptionBuilder');
        $validationRuleBuilder = $objectManager->getObject(
            'Magento\Customer\Service\V1\Data\Eav\ValidationRuleBuilder'
        );

        $attributeMetadataBuilder = $objectManager->getObject(
            '\Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder',
            ['optionBuilder' => $optionBuilder, 'validationRuleBuilder' => $validationRuleBuilder]
        )->populateWithArray(
            array(
                'attribute_code' => self::ATTRIBUTE_CODE,
                'frontend_input' => self::FRONTEND_INPUT,
                'input_filter' => self::INPUT_FILTER,
                'store_label' => self::STORE_LABEL,
                'validation_rules' => array(),
                'options' => $options
            )
        );
        $attributeMetadata = new AttributeMetadata($attributeMetadataBuilder);

        $this->assertSame(self::ATTRIBUTE_CODE, $attributeMetadata->getAttributeCode());
        $this->assertSame(self::FRONTEND_INPUT, $attributeMetadata->getFrontendInput());
        $this->assertSame(self::INPUT_FILTER, $attributeMetadata->getInputFilter());
        $this->assertSame(self::STORE_LABEL, $attributeMetadata->getStoreLabel());
        $this->assertSame(array(), $attributeMetadata->getValidationRules());
        $this->assertSame($options[0], $attributeMetadata->getOptions()[0]->__toArray());
        $this->assertSame($options[1], $attributeMetadata->getOptions()[1]->__toArray());
    }
}
