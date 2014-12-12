<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Message;

/**
 * \Magento\Framework\Message\Warning test case
 */
class WarningTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\Warning
     */
    protected $model;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Framework\Message\Warning');
    }

    public function testGetType()
    {
        $this->assertEquals(MessageInterface::TYPE_WARNING, $this->model->getType());
    }
}
