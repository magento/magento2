<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Layout;

use Magento\Framework\View\EntitySpecificHandlesList;

/**
 * Plugin for @see \Magento\Framework\View\Model\Layout\Merge
 */
class MergePlugin
{
    /**
     * @var EntitySpecificHandlesList
     */
    private $entitySpecificHandlesList;

    /**
     * Constructor
     *
     * @param EntitySpecificHandlesList $entitySpecificHandlesList
     */
    public function __construct(
        EntitySpecificHandlesList $entitySpecificHandlesList
    ) {
        $this->entitySpecificHandlesList = $entitySpecificHandlesList;
    }

    /**
     * Make sure that page specific handles (those which contain entity ID) do not have any declarations of ESI blocks
     *
     * ESI blocks cannot be declared for page specific handles, otherwise they will not be shared between pages
     *
     * @param \Magento\Framework\View\Model\Layout\Merge $subject
     * @param string $handle
     * @param string $updateXml
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeValidateUpdate(\Magento\Framework\View\Model\Layout\Merge $subject, $handle, $updateXml)
    {
        if (in_array($handle, $this->entitySpecificHandlesList->getHandles())
            && ($updateXml && strpos($updateXml, 'ttl=') !== false)
        ) {
            throw new \LogicException(
                "Handle '{$handle}' must not contain blocks with 'ttl' attribute specified. "
                . "Otherwise, these blocks will be treated as ESI by Varnish, however will not be shared between pages "
                . "because handle '{$handle}' is not generic. Such blocks will not be rendered on the page"
            );
        }
        return null;
    }
}
