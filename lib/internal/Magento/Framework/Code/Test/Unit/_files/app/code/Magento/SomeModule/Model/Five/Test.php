<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SomeModule\Model\Five;

require_once __DIR__ . '/../Three/Test.php';
require_once __DIR__ . '/../Proxy.php';
class Test extends \Magento\SomeModule\Model\Three\Test
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
     */
    public function __construct(\Magento\SomeModule\Model\Proxy $proxy)
    {
        parent::__construct($proxy);
    }
}
