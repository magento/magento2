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
namespace Magento\Ui\ContentType;

use Magento\Framework\ObjectManager;

/**
 * Class ContentTypeFactory
 */
class ContentTypeFactory
{
    /**
     * Default content type
     */
    const DEFAULT_TYPE = 'html';

    /**
     * Content types
     *
     * @var array
     */
    protected $types = [
        'html' => 'Magento\Ui\ContentType\Html',
        'json' => 'Magento\Ui\ContentType\Json',
        'xml' => 'Magento\Ui\ContentType\Xml',
    ];

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     * @param array $types
     */
    public function __construct(ObjectManager $objectManager, array $types = [])
    {
        $this->types = array_merge($this->types, $types);
        $this->objectManager = $objectManager;
    }

    /**
     * Get content type object instance
     *
     * @param string $type
     * @return ContentTypeInterface
     * @throws \InvalidArgumentException
     */
    public function get($type = ContentTypeFactory::DEFAULT_TYPE)
    {
        if (!isset($this->types[$type])) {
            throw new \InvalidArgumentException(sprintf("Wrong content type '%s', renderer not exists.", $type));
        }

        $contentRender = $this->objectManager->get($this->types[$type]);
        if (!$contentRender instanceof ContentTypeInterface) {
            throw new \InvalidArgumentException(
                sprintf('"%s" must implement the interface ContentTypeInterface.', $this->types[$type])
            );
        }

        return $contentRender;
    }
}
