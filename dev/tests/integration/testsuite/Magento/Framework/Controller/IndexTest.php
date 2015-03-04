<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Controller;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testNotFoundAction()
    {
        $this->dispatch('framework/index/notFound');
        $this->assertEquals('404', $this->getResponse()->getHttpResponseCode());
        $this->assertEquals('Requested resource not found', $this->getResponse()->getBody());
    }
}
