<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @codingStandardsIgnoreStart
 * Coding Standards have to be ignored in this file, as it is just a data source for tests.
 */

namespace Magento\SomeModule\Model;

use Magento\SomeModule\Model\Two\Test as TestTwo;
use Magento\SomeModule\Model\Three\Test as TestThree;

/**
 * Interface short description.
 *
 * Interface long
 * description.
 *
 * @tag1 data1
 * @tag2 data2
 */
interface SevenInterface extends \Magento\Framework\Code\Generator\CodeGeneratorInterface
{

    /**
     * Method short description
     *
     * @param array $data
     * @return TestThree
     */
    public static function testMethod1(array &$data = []);

    /**
     * Method short description
     *
     * Method long
     * description
     *
     * @param string $data
     * @param bool $flag
     */
    public function testMethod2($data = 'test_default', $flag = true);

    public function testMethod3();


}
