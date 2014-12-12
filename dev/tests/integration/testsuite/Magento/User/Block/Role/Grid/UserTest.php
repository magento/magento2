<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\User\Block\Role\Grid;

/**
 * @magentoAppArea adminhtml
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\User\Block\Role\Grid\User
     */
    protected $_block;

    protected function setUp()
    {
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $this->_block = $layout->createBlock('Magento\User\Block\Role\Grid\User');
    }

    public function testPreparedCollection()
    {
        $this->_block->toHtml();
        $this->assertInstanceOf('Magento\User\Model\Resource\Role\User\Collection', $this->_block->getCollection());
    }
}
