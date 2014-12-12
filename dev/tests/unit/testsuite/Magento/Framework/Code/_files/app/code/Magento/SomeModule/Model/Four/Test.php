<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\SomeModule\Model\Four;

require_once __DIR__ . '/../One/Test.php';
require_once __DIR__ . '/../ElementFactory.php';
require_once __DIR__ . '/../Proxy.php';
class Test extends \Magento\SomeModule\Model\One\Test
{
    /**
     * @var \Magento\SomeModule\Model\ElementFactory
     */
    protected $_factory;

    public function __construct(
        \Magento\SomeModule\Model\Proxy $proxy,
        \Magento\SomeModule\Model\ElementFactory $factory
    ) {
        $this->_factory = $factory;
        parent::__construct($proxy, $factory);
    }
}
