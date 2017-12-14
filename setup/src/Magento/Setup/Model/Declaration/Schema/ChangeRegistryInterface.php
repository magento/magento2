<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Structure;

/**
 * ChangeRegistry is type of classes, that holds all information
 * that need to be changed from one installation to another
 */
interface ChangeRegistryInterface
{
    /**
     * For elements that needs to be created
     */
    const CREATE_OPERATION = 'create';

    /**
     * For elements that needs to be changed
     */
    const CHANGE_OPERATION = 'change';

    /**
     * For elements that needs to be removed
     */
    const REMOVE_OPERATION = "remove";

    /**
     * For elements that needs to be renamed
     */
    const RENAME_OPERAION = "rename";

    /**
     * Retrieve operations by type
     *
     * @param string $type
     * @param string $operation
     * @return array
     */
    public function get($operation, $type = null);

    /**
     * Register operation
     *
     * @param ElementInterface|object $dtoObject
     * @param string $type
     * @param string $operation
     * @param ElementInterface $oldDtoObject
     * @return void
     */
    public function register(
        ElementInterface $dtoObject,
        $type,
        $operation,
        ElementInterface $oldDtoObject = null
    );

    /**
     * Register current state of structure to registry
     *
     * @param Structure $structure
     * @return void
     */
    public function registerStructure(Structure $structure);

    /**
     * Retrieve current structure object
     *
     * @return Structure
     */
    public function getCurrentStructureState();

    /**
     * Return current installation request
     *
     * @return Request
     */
    public function getCurrentInstallationRequest();

    /**
     * Register installation request with all needed options
     *
     * @param Request $request
     * @return void
     */
    public function registerInstallationRequest(Request $request);
}
