<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Block;

/**
 * Class JavascriptTest
 */
class JavascriptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Block\Javascript
     */
    protected $javascript;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);

        $this->javascript = $objectManager->create(
            \Magento\PageCache\Block\Javascript::class
        );
    }

    public function testGetScriptOptions()
    {
        $this->request->getQuery()->set('getparameter', 1);
        $this->assertContains('?getparameter=1', $this->javascript->getScriptOptions());
    }
}
