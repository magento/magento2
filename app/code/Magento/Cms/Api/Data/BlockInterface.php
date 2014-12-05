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
namespace Magento\Cms\Api\Data;

/**
 * Interface BlockInterface
 */
interface BlockInterface
{
    const ID = 'block_id';

    const IDENTIFIER = 'identifier';

    const TITLE = 'title';

    const CONTENT = 'content';

    const CREATION_TIME = 'creation_time';

    const UPDATE_TIME ='update_time';

    const IS_ACTIVE ='is_active';

    /**
     * Retrieve block id
     *
     * @return int
     */
    public function getId();

    /**
     * Retrieve block identifier
     *
     * @return int
     */
    public function getIdentifier();

    /**
     * Retrieve block title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Retrieve block content
     *
     * @return string
     */
    public function getContent();

    /**
     * Retrieve block creation time
     *
     * @return string
     */
    public function getCreationTime();

    /**
     * Retrieve block update time
     *
     * @return string
     */
    public function getUpdateTime();

    /**
     * Retrieve block status
     *
     * @return int
     */
    public function getIsActive();
}
