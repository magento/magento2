<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Express;

class ShortcutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Alias
     */
    const ALIAS = 'alias';

    public function testGetAlias()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $helper->getObject('Magento\Paypal\Block\Express\Shortcut', ['alias' => self::ALIAS]);
        $this->assertEquals(self::ALIAS, $model->getAlias());
    }
}
