<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests for minifier
 */
class FileSystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;


    protected function setUp()
    {
        $this->objectManager = Bootstrap::getInstance()->getObjectManager();
        $this->objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
    }

    /**
     * CSS Minifier library test
     *
     * When fails on library update or minification handler replacement:
     * 1 - minify `_files/static/css/styles.css` with new library manually
     * 2 - use DIFF tools to see difference between new minified CSS and old minified one
     * 3 - ensure that all differences are acceptable
     * 4 - ensure that new minified CSS is fully workable in all supported browsers
     * 5 - replace `_files/static/css/styles.magento.min.css` with new minified css
     */
    public function testCssMinifierLibrary()
    {
        /** @var \Magento\Core\Model\Asset\Config $config */
        $config = $this->objectManager->get('\Magento\Core\Model\Asset\Config');
        $adapterClass = $config->getAssetMinificationAdapter('css');

        /** @var \Magento\Framework\Code\Minifier\AdapterInterface $adapter */
        $adapter = $this->objectManager->get($adapterClass);
        $this->assertEquals(
            file_get_contents(dirname(__DIR__) . '/_files/static/css/styles.magento.min.css'),
            $adapter->minify(file_get_contents(dirname(__DIR__) . '/_files/static/css/styles.css')),
            'Minified CSS differs from initial minified CSS snapshot. '
            . 'Ensure that new CSS is fully valid for all supported browsers '
            . 'and replace old minified snapshot with new one.'
        );
    }
}
