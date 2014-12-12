<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Test class for \Magento\Customer\Block\Widget\Dob
 */
namespace Magento\Customer\Block\Widget;

class DobTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDateFormat()
    {
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Block\Widget\Dob'
        );
        $this->assertNotEmpty($block->getDateFormat());
    }
}
