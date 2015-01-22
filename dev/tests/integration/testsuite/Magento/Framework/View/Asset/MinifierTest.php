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
class MinifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Request path for test minifier
     */
    const REQUEST_PATH = '/frontend/Magento/blank/en_US/css/styles.css';

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

    /**
     * @magentoConfigFixture current_store dev/css/minify_files 1
     */
    public function testCssMinification()
    {
        /** @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject $appState */
        $appState = $this->getMock('\Magento\Framework\App\State', ['getMode'], [], '', false);
        $appState->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue(\Magento\Framework\App\State::MODE_DEFAULT));

        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->objectManager->get('Magento\Framework\App\Request\Http');
        $request->setRequestUri(self::REQUEST_PATH);
        $request->setParam('resource', self::REQUEST_PATH);

        $response = $this->getMockForAbstractClass(
            'Magento\Framework\App\Response\FileInterface',
            [],
            '',
            false,
            false,
            true,
            ['setFilePath']
        );
        $response->expects(
            $this->any()
        )->method(
            'setFilePath'
        )->will(
            $this->returnCallback(
                function ($path) {
                    $this->assertEquals(
                        file_get_contents(dirname(__DIR__) . '/_files/static/css/styles.magento.min.css'),
                        file_get_contents($path),
                        'Minified files are not equal or minification did not work for requested CSS'
                    );
                }
            )
        );

        $publisher = $this->objectManager->create(
            'Magento\Framework\App\View\Asset\Publisher',
            [
                'appState' => $appState
            ]
        );

        /** @var \Magento\Framework\App\StaticResource $staticResourceApp */
        $staticResourceApp = $this->objectManager->create(
            'Magento\Framework\App\StaticResource',
            [
                'response' => $response,
                'publisher' => $publisher
            ]
        );
        $initParams = Bootstrap::getInstance()->getAppInitParams();
        $designPath = $initParams[\Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS]['design']['path'];
        $destFile = $designPath . '/frontend/Magento/blank/web/css/styles.css';

        if (!is_readable(dirname($destFile))) {
            mkdir(dirname($destFile), 777, true);
        }
        
        copy(dirname(__DIR__) . '/_files/static/css/styles.css', $destFile);

        $staticResourceApp->launch();

        unlink($destFile);
    }
}
