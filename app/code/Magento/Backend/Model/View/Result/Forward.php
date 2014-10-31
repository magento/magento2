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
namespace Magento\Backend\Model\View\Result;

use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Backend\App\AbstractAction;

class Forward extends \Magento\Framework\Controller\Result\Forward
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;

    /**
     * @param RequestInterface $request
     * @param Session $session
     * @param ActionFlag $actionFlag
     */
    public function __construct(RequestInterface $request, Session $session, ActionFlag $actionFlag)
    {
        $this->session = $session;
        $this->actionFlag = $actionFlag;
        parent::__construct($request);
    }

    /**
     * @param string $action
     * @return $this
     */
    public function forward($action)
    {
        $this->session->setIsUrlNotice($this->actionFlag->get('', AbstractAction::FLAG_IS_URLS_CHECKED));
        return parent::forward($action);
    }
}
