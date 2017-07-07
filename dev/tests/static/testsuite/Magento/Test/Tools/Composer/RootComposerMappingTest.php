<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Tools\Composer;

use Magento\Tools\Composer\Package\Reader;

/**
 * Class RootComposerMappingTest
 */
class RootComposerMappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test existence of paths for marshalling
     * @return void
     */
    public function testMapping()
    {
        //Checking existence of composer components
        $reader = new Reader(BP . '/dev/tools/Magento/Tools/Composer');
        $patterns = $reader->getPatterns();
        $counter = 0;
        $count = count($patterns);
        for ($i = 0; $i < $count; $i++) {
            if (file_exists(BP . '/' . $patterns[$i])) {
                $counter++;
            }
        }

        $this->assertEquals($count, $counter);

        //Checking existence of customizable paths
        $customizablePaths = $reader->getCustomizablePaths();
        $counter = 0;
        $count = count($customizablePaths);
        for ($i = 0; $i < $count; $i++) {
            if (file_exists(BP . '/' . str_replace('*', '', $customizablePaths[$i]))) {
                $counter++;
            }
        }

        $this->assertEquals($count, $counter);
    }
}
