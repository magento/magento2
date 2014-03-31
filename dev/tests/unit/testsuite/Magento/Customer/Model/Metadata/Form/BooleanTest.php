<?php
/**
 * test Magento\Customer\Model\Metadata\Form\Boolean
 *
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
namespace Magento\Customer\Model\Metadata\Form;

class BooleanTest extends AbstractFormTestCase
{
    /**
     * @param mixed $value to assign to boolean
     * @param mixed $expected text output
     * @dataProvider getOptionTextDataProvider
     */
    public function testGetOptionText($value, $expected)
    {
        // calling outputValue() will cause the protected method getOptionText() to be called
        $boolean = new Boolean(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMetadataMock,
            $this->localeResolverMock,
            $value,
            0
        );
        $this->assertSame($expected, $boolean->outputValue());
    }

    public function getOptionTextDataProvider()
    {
        return array(
            '0' => array('0', 'No'),
            '1' => array('1', 'Yes'),
            'int 5' => array(5, ''),
            'Null' => array(null, ''),
            'Invalid' => array('Invalid', ''),
            'Empty string' => array('', '')
        );
    }
}
