<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\File\Validator;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests protected extensions.
 */
class NotProtectedExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that phpt, pht is invalid extension type
     * @dataProvider isValidDataProvider
     * @param string $extension
     * @return void
     */
    public function testIsValid($extension)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $model */
        $model = $objectManager->create(\Magento\MediaStorage\Model\File\Validator\NotProtectedExtension::class);
        $this->assertFalse($model->isValid($extension));
    }

    /**
     * Data provider for testIsValid
     *
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            ['phpt'],
            ['pht'],
        ];
    }
}
