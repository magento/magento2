<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Controller;

class AjaxTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($postData)
    {
        $this->getRequest()->setPostValue('translate', $postData);
        $this->dispatch('translation/ajax/index');
        $this->assertEquals('{success:true}', $this->getResponse()->getBody());
    }

    public function indexActionDataProvider()
    {
        return [['test'], [['test']]];
    }
}
