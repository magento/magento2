<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Structure;
use Magento\Setup\Model\Declaration\Schema\Dto\TableElementInterface;

/**
 * Holds information about all changes between 2 schemas: db and declaration XML
 * Holds 2 items:
 *  - new (Should be changed to)
 *  - old (Was)
 */
class ChangeRegistry implements ChangeRegistryInterface
{
    /**
     * @var array
     */
    private $changes;

    /**
     * @var array
     */
    private $whiteListTables = [];

    /**
     * @var Structure
     */
    private $structure;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(ComponentRegistrar $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * @inheritdoc
     */
    public function get($operation, $type = null)
    {
        if (empty($type)) {
            return isset($this->changes[$operation]) ? $this->changes[$operation] : [];
        }

        return isset($this->changes[$operation][$type]) ? $this->changes[$operation][$type] : [];
    }

    /**
     * Retrieve array of whitelisted tables
     * Whitelist tables should have JSON format and should be added through
     * CLI command: should be done in next story
     *
     *
     * @return array
     */
    private function getWhiteListTables()
    {
        if (!$this->whiteListTables) {
            foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $path) {
                $whiteListPath = $path . DIRECTORY_SEPARATOR . 'etc' .
                    DIRECTORY_SEPARATOR . 'declarative_schema_white_list.json';

                if (file_exists($whiteListPath)) {
                    $this->whiteListTables = array_replace_recursive(
                        $this->whiteListTables,
                        json_decode(file_get_contents($whiteListPath), true)
                    );
                }
            }
        }

        return $this->whiteListTables;
    }

    /**
     * Check whether element can be registered
     *
     * For example, if element is not in whitelist.json it cant
     * be registered due to backward incompatability
     *
     * @param ElementInterface $object
     * @return bool
     */
    private function canBeRegistered(ElementInterface $object)
    {
        $whiteList = $this->getWhiteListTables();
        $type = $object->getElementType();

        if ($object instanceof TableElementInterface) {
            return isset($whiteList['table'][$object->getTable()->getName()][$type][$object->getName()]);
        }

        return isset($whiteList['table'][$object->getName()]);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function registerInstallationRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function register(ElementInterface $dtoObject, $type, $operation, ElementInterface $oldDtoObject = null)
    {
        //Comment until whitelist functionality will be done
        if (!$this->canBeRegistered($dtoObject)) {
            #return $this; //ignore any operations for non registered elements changes
        }

        $this->changes[$operation][$type][] = [
            'new' => $dtoObject,
            'old' => $oldDtoObject
        ];

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function registerStructure(Structure $structure)
    {
        $this->structure = $structure;
    }

    /**
     * Retrieve current structure
     * This function needs for rollback functionality
     *
     * @return Structure
     */
    public function getCurrentStructureState()
    {
        return $this->structure;
    }

    /**
     * Request holds some information from cli command or UI
     * like: save mode or dry-run mode
     *
     * @return Request
     */
    public function getCurrentInstallationRequest()
    {
        return $this->request;
    }
}
