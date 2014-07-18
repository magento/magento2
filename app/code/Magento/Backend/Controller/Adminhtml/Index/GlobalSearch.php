<?php
/**
 *
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
namespace Magento\Backend\Controller\Adminhtml\Index;

class GlobalSearch extends \Magento\Backend\Controller\Adminhtml\Index
{
    /**
     * Search modules list
     *
     * @var array
     */
    protected $_searchModules;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param array $searchModules
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, array $searchModules = array())
    {
        $this->_searchModules = $searchModules;
        parent::__construct($context);
    }

    /**
     * Global Search Action
     *
     * @return void
     */
    public function execute()
    {
        $items = array();

        if (!$this->_authorization->isAllowed('Magento_Adminhtml::global_search')) {
            $items[] = array(
                'id' => 'error',
                'type' => __('Error'),
                'name' => __('Access Denied'),
                'description' => __('You need more permissions to do this.')
            );
        } else {
            if (empty($this->_searchModules)) {
                $items[] = array(
                    'id' => 'error',
                    'type' => __('Error'),
                    'name' => __('No search modules were registered'),
                    'description' => __(
                        'Please make sure that all global admin search modules are installed and activated.'
                    )
                );
            } else {
                $start = $this->getRequest()->getParam('start', 1);
                $limit = $this->getRequest()->getParam('limit', 10);
                $query = $this->getRequest()->getParam('query', '');
                foreach ($this->_searchModules as $searchConfig) {

                    if ($searchConfig['acl'] && !$this->_authorization->isAllowed($searchConfig['acl'])) {
                        continue;
                    }

                    $className = $searchConfig['class'];
                    if (empty($className)) {
                        continue;
                    }
                    $searchInstance = $this->_objectManager->create($className);
                    $results = $searchInstance->setStart(
                        $start
                    )->setLimit(
                        $limit
                    )->setQuery(
                        $query
                    )->load()->getResults();
                    $items = array_merge_recursive($items, $results);
                }
            }
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($items)
        );
    }
}
