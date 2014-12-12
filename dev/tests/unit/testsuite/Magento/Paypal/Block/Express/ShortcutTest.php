<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Block\Express;

class ShortcutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Alias
     */
    const ALIAS = 'alias';

    public function testGetAlias()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $model = $helper->getObject('Magento\Paypal\Block\Express\Shortcut', ['alias' => self::ALIAS]);
        $this->assertEquals(self::ALIAS, $model->getAlias());
    }
}
