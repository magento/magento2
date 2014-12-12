<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Model\Config\Source\Group;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class \Magento\Customer\Model\Config\Source\Group\Multiselect
 */
class MultiselectTest extends \PHPUnit_Framework_TestCase
{
    public function testToOptionArray()
    {
        /** @var Multiselect $multiselect */
        $multiselect = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Config\Source\Group\Multiselect');
        $this->assertEquals(
            [
                ['value' => 1, 'label' => 'General'],
                ['value' => 2, 'label' => 'Wholesale'],
                ['value' => 3, 'label' => 'Retailer'],
            ],
            $multiselect->toOptionArray()
        );
    }
}
