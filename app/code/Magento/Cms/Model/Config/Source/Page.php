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
namespace Magento\Cms\Model\Config\Source;

/**
 * Class Page
 */
class Page implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magento\Cms\Model\PageRepository
     */
    protected $pageRepository;

    /**
     * @var \Magento\Cms\Model\Resource\PageCriteria
     */
    protected $pageCriteriaFactory;

    /**
     * @param \Magento\Cms\Model\PageRepository $pageRepository
     * @param \Magento\Cms\Model\Resource\PageCriteriaFactory $pageCriteriaFactory
     */
    public function __construct(
        \Magento\Cms\Model\PageRepository $pageRepository,
        \Magento\Cms\Model\Resource\PageCriteriaFactory $pageCriteriaFactory
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageCriteriaFactory = $pageCriteriaFactory;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->pageRepository->getList($this->pageCriteriaFactory->create())->toOptionIdArray();
        }
        return $this->options;
    }
}
