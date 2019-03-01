<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Code\File\Validator;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests protected extensions.
 */
class NotProtectedExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that phpt, pht are invalid extension types.
     *
     * @dataProvider isValidDataProvider
     * @param string $extension
     * @return void
     */
    public function testIsValid(string $extension)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $model */
        $model = $objectManager->create(\Magento\MediaStorage\Model\File\Validator\NotProtectedExtension::class);
        $this->assertFalse($model->isValid($extension));
    }

    /**
     * @return array
     */
    public function isValidDataProvider(): array
    {
        return [
            ['phpt'],
            ['pht'],
        ];
    }
}
