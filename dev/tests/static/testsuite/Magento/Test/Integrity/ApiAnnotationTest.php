<?php
/**
 * Scan source code for unmarked API interfaces
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;

class ApiAnnotationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * API annotation pattern
     */
    private $apiAnnotation  = '~/\*{2}(.*@api.*)\*/\s+(?=interface)~s';

    public function testApiAnnotations()
    {
        $modulePaths = array_map(function ($path) {
            return $path . DIRECTORY_SEPARATOR .  'Api';
        }, (new ComponentRegistrar())->getPaths(ComponentRegistrar::MODULE));

        foreach (Files::init()->getFiles($modulePaths, '*.php', true) as $file) {
            $fileContent = file_get_contents($file);
            if (!preg_match($this->apiAnnotation, $fileContent)) {
                $result[] = $file;
            }
        }
        if (!empty($result)) {
            $this->fail(sprintf(
                'Found %s file(s) without @api annotations under Api namespace: %s',
                count($result),
                PHP_EOL . implode(PHP_EOL, $result)
            ));
        }
    }
}
