<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        array $data = []
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
