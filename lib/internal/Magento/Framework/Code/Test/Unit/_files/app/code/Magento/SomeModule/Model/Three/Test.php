<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SomeModule\Model\Three;

require_once __DIR__ . '/../Two/Test.php';
require_once __DIR__ . '/../ElementFactory.php';
require_once __DIR__ . '/../Proxy.php';
class Test extends \Magento\SomeModule\Model\Two\Test
{
    /**
     * @var \Magento\SomeModule\Model\ElementFactory
     */
    protected $_factory;

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
     * @param \Magento\SomeModule\Model\ElementFactory $factory
     */
    public function __construct(
        \Magento\SomeModule\Model\Proxy $proxy,
        \Magento\SomeModule\Model\ElementFactory $factory
    ) {
        $this->_factory = $factory;
        parent::__construct($proxy);
    }
}
