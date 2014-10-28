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
namespace Magento\Sales\Service\V1\Data;

/**
 * Class CreditmemoMapper
 */
class CreditmemoMapper
{
    /**
     * @var CreditmemoBuilder
     */
    protected $creditmemoBuilder;

    /**
     * @var CreditmemoItemMapper
     */
    protected $creditmemoItemMapper;

    /**
     * @param CreditmemoBuilder $creditmemoBuilder
     * @param CreditmemoItemMapper $creditmemoItemMapper
     */
    public function __construct(CreditmemoBuilder $creditmemoBuilder, CreditmemoItemMapper $creditmemoItemMapper)
    {
        $this->creditmemoBuilder = $creditmemoBuilder;
        $this->creditmemoItemMapper = $creditmemoItemMapper;
    }

    /**
     * Returns array of items
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return \Magento\Sales\Service\V1\Data\CreditmemoItem[]
     */
    protected function getItems(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $items = [];
        foreach ($creditmemo->getAllItems() as $item) {
            $items[] = $this->creditmemoItemMapper->extractDto($item);
        }

        return $items;
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return \Magento\Framework\Service\Data\AbstractExtensibleObject
     */
    public function extractDto(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $this->creditmemoBuilder->populateWithArray($creditmemo->getData());
        $this->creditmemoBuilder->setItems($this->getItems($creditmemo));

        return $this->creditmemoBuilder->create();
    }
}
