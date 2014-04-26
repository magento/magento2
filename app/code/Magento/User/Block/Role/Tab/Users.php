<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\User\Block\Role\Tab;

/**
 * Class \Magento\User\Block\Role\Tab\Users
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Users extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * User model factory
     *
     * @var \Magento\User\Model\Resource\User\CollectionFactory
     */
    protected $_userCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\User\Model\Resource\User\CollectionFactory $userCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\User\Model\Resource\User\CollectionFactory $userCollectionFactory,
        array $data = array()
    ) {
        // _userCollectionFactory is used in parent::__construct
        $this->_userCollectionFactory = $userCollectionFactory;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $roleId = $this->getRequest()->getParam('rid', false);
        /** @var \Magento\User\Model\Resource\User\Collection $users */
        $users = $this->_userCollectionFactory->create()->load();
        $this->setTemplate('role/users.phtml')->assign('users', $users->getItems())->assign('roleId', $roleId);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'userGrid',
            $this->getLayout()->createBlock('Magento\User\Block\Role\Grid\User', 'roleUsersGrid')
        );
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('userGrid');
    }
}
