<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Code\File\Validator;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class NotProtectedExtension
 */
class NotProtectedExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test that phpt, pht is invalid extension type
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($extension)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $model */
        $model = $objectManager->create(\Magento\MediaStorage\Model\File\Validator\NotProtectedExtension::class);
        $this->assertFalse($model->isValid($extension));
    }

    public static function isValidDataProvider()
    {
        return [
            ['phpt'],
            ['pht']
        ];
    }
}
