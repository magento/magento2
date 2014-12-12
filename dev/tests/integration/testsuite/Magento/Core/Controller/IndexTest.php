<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Core\Controller;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testNotFoundAction()
    {
        $this->dispatch('core/index/notFound');
        $this->assertEquals('404', $this->getResponse()->getHttpResponseCode());
        $this->assertEquals('Requested resource not found', $this->getResponse()->getBody());
    }
}
