<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Code\File\Validator;

use Magento\TestFramework\Helper\Bootstrap;

/**
<<<<<<< HEAD
 * Tests protected extensions.
=======
 * Class NotProtectedExtension
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class NotProtectedExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
<<<<<<< HEAD
     * Tests that phpt, pht are invalid extension types.
     *
     * @dataProvider isValidDataProvider
     * @param string $extension
     * @return void
     */
    public function testIsValid(string $extension)
=======
     * Test that phpt, pht is invalid extension type
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($extension)
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $model */
        $model = $objectManager->create(\Magento\MediaStorage\Model\File\Validator\NotProtectedExtension::class);
        $this->assertFalse($model->isValid($extension));
    }

<<<<<<< HEAD
    /**
     * @return array
     */
    public function isValidDataProvider(): array
    {
        return [
            ['phpt'],
            ['pht'],
=======
    public function isValidDataProvider()
    {
        return [
            ['phpt'],
            ['pht']
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        ];
    }
}
