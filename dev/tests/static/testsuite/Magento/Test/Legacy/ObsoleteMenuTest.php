<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Legacy tests to find obsolete menu declaration
 */
namespace Magento\Test\Legacy;

class ObsoleteMenuTest extends \PHPUnit\Framework\TestCase
{
    public function testMenuDeclaration()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $menuFile
             */
            function ($menuFile) {
                $menuXml = simplexml_load_file($menuFile);
                $xpath = '/config/menu/*[boolean(./children) or boolean(./title) or boolean(./action)]';
                $this->assertEmpty(
                    $menuXml->xpath($xpath),
                    'Obsolete menu structure detected in file ' . $menuFile . '.'
                );
            },
            \Magento\Framework\App\Utility\Files::init()->getMainConfigFiles()
        );
    }
}
