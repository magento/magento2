<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Legacy tests to find obsolete menu declaration
 */
namespace Magento\Test\Legacy;

class ObsoleteMenuTest extends \PHPUnit_Framework_TestCase
{
    public function testMenuDeclaration()
    {
        $invoker = new \Magento\Framework\Test\Utility\AggregateInvoker($this);
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
            \Magento\Framework\Test\Utility\Files::init()->getMainConfigFiles()
        );
    }
}
