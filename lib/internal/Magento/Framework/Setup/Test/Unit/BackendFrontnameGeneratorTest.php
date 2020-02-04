<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\Setup\BackendFrontnameGenerator;

class BackendFrontnameGeneratorTest extends \PHPUnit\Framework\TestCase
{
    public function testGenerate()
    {
        $regexp = '/' . BackendFrontnameGenerator::ADMIN_AREA_PATH_PREFIX
            . '[a-z0-9]{1,' . BackendFrontnameGenerator::ADMIN_AREA_PATH_RANDOM_PART_LENGTH .'}/';

        $this->assertRegExp($regexp, BackendFrontnameGenerator::generate(), 'Unexpected Backend Frontname pattern.');
    }
}
