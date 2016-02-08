<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Config\Source;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class \Magento\Security\Model\Config\Source\ResetMethod
 */
class Checker extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Security\Block\Adminhtml\Js\Checker
     */
    protected $block;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * Set up
     */
    protected function setUp()
    {
        parent::setUp();

        /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        $this->block = $objectManager->get('Magento\Security\Block\Adminhtml\Js\Checker');
        $this->urlBuilder = $objectManager->get('Magento\Framework\UrlInterface');
        $this->jsonEncoder = $objectManager->get('Magento\Framework\Json\EncoderInterface');
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        $this->block = null;
        $this->urlBuilder = null;
        $this->jsonEncoder = null;
        parent::tearDown();
    }

    /**
     * test for getSessionCheckerJson() method
     */
    public function testGetSessionCheckerJson()
    {
        $expectedJson = $this->jsonEncoder->encode(
            [
                'requestUrl' => $this->urlBuilder->getUrl('security/session/check'),
                'redirectUrl' => $this->urlBuilder->getUrl('adminhtml/')
            ]
        );

        $this->assertEquals($expectedJson, $this->block->getSessionCheckerJson());
    }
}
