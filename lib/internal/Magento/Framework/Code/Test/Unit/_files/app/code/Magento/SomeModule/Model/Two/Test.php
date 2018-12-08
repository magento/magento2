<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SomeModule\Model\Two;

require_once __DIR__ . '/../One/Test.php';
require_once __DIR__ . '/../Proxy.php';
class Test extends \Magento\SomeModule\Model\One\Test
{
    /**
     * @var \Magento\SomeModule\Model\Proxy
     */
    protected $_proxy;

    /**
     * Test constructor.
<<<<<<< HEAD
     *
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @param \Magento\SomeModule\Model\Proxy $proxy
     * @param array $data
     */
    public function __construct(\Magento\SomeModule\Model\Proxy $proxy, $data = [])
    {
        $this->_proxy = $proxy;
    }
}
