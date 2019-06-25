<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Controller\Adminhtml\System\Config;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Config\Controller\Adminhtml\System\AbstractConfig;

/**
 * System Configuration Save Controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends AbstractConfig implements HttpPostActionInterface
{
    /**
     * Backend Config Model Factory
     *
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $_configFactory;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
     * @param \Magento\Config\Model\Config\Factory $configFactory
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\Stdlib\StringUtils $string
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\Stdlib\StringUtils $string
    ) {
        parent::__construct($context, $configStructure, $sectionChecker);
        $this->_configFactory = $configFactory;
        $this->_cache = $cache;
        $this->string = $string;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed() && $this->isSectionAllowed();
    }

    /**
     * Checks if user has access to section.
     *
     * @return bool
     */
    private function isSectionAllowed(): bool
    {
        $sectionId = $this->_request->getParam('section');
        $isAllowed = $this->_configStructure->getElement($sectionId)->isAllowed();
        if (!$isAllowed) {
            $groups = $this->getRequest()->getPost('groups');
            $fieldPath = $this->getFirstFieldPath($groups, $sectionId);

            $fieldPaths = $this->_configStructure->getFieldPaths();
            $fieldPath = $fieldPaths[$fieldPath][0] ?? $sectionId;
            $explodedConfigPath = explode('/', $fieldPath);
            $configSectionId = $explodedConfigPath[0] ?? $sectionId;

            $isAllowed = $this->_configStructure->getElement($configSectionId)->isAllowed();
        }

        return $isAllowed;
    }

    /**
     * Return field path as string.
     *
     * @param array $elements
     * @param string $fieldPath
     * @return string
     */
    private function getFirstFieldPath(array $elements, string $fieldPath): string
    {
        $groupData = [];
        foreach ($elements as $elementName => $element) {
            if (!empty($element)) {
                $fieldPath .= '/' . $elementName;

                if (!empty($element['fields'])) {
                    $groupData = $element['fields'];
                } elseif (!empty($element['groups'])) {
                    $groupData = $element['groups'];
                }

                if (!empty($groupData)) {
                    $fieldPath = $this->getFirstFieldPath($groupData, $fieldPath);
                }
                break;
            }
        }

        return $fieldPath;
    }

    /**
     * Get groups for save
     *
     * @return array|null
     */
    protected function _getGroupsForSave()
    {
        $groups = $this->getRequest()->getPost('groups');
        $files = $this->getRequest()->getFiles('groups');

        if ($files && is_array($files)) {
            /**
             * Carefully merge $_FILES and $_POST information
             * None of '+=' or 'array_merge_recursive' can do this correct
             */
            foreach ($files as $groupName => $group) {
                $data = $this->_processNestedGroups($group);
                if (!empty($data)) {
                    if (!empty($groups[$groupName])) {
                        $groups[$groupName] = array_merge_recursive((array)$groups[$groupName], $data);
                    } else {
                        $groups[$groupName] = $data;
                    }
                }
            }
        }
        return $groups;
    }

    /**
     * Process nested groups
     *
     * @param mixed $group
     * @return array
     */
    protected function _processNestedGroups($group)
    {
        $data = [];

        if (isset($group['fields']) && is_array($group['fields'])) {
            foreach ($group['fields'] as $fieldName => $field) {
                if (!empty($field['value'])) {
                    $data['fields'][$fieldName] = ['value' => $field['value']];
                }
            }
        }

        if (isset($group['groups']) && is_array($group['groups'])) {
            foreach ($group['groups'] as $groupName => $groupData) {
                $nestedGroup = $this->_processNestedGroups($groupData);
                if (!empty($nestedGroup)) {
                    $data['groups'][$groupName] = $nestedGroup;
                }
            }
        }

        return $data;
    }

    /**
     * Custom save logic for section
     *
     * @return void
     */
    protected function _saveSection()
    {
        $method = '_save' . $this->string->upperCaseWords($this->getRequest()->getParam('section'), '_', '');
        if (method_exists($this, $method)) {
            $this->{$method}();
        }
    }

    /**
     * Advanced save procedure
     *
     * @return void
     */
    protected function _saveAdvanced()
    {
        $this->_cache->clean();
    }

    /**
     * Save configuration
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            // custom save logic
            $this->_saveSection();
            $section = $this->getRequest()->getParam('section');
            $website = $this->getRequest()->getParam('website');
            $store = $this->getRequest()->getParam('store');
            $configData = [
                'section' => $section,
                'website' => $website,
                'store' => $store,
                'groups' => $this->_getGroupsForSave(),
            ];
            $configData = $this->filterNodes($configData);

            /** @var \Magento\Config\Model\Config $configModel */
            $configModel = $this->_configFactory->create(['data' => $configData]);
            $configModel->save();
            $this->_eventManager->dispatch('admin_system_config_save', [
                'configData' => $configData,
                'request' => $this->getRequest()
            ]);
            $this->messageManager->addSuccess(__('You saved the configuration.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $messages = explode("\n", $e->getMessage());
            foreach ($messages as $message) {
                $this->messageManager->addError($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while saving this configuration:') . ' ' . $e->getMessage()
            );
        }

        $this->_saveState($this->getRequest()->getPost('config_state'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath(
            'adminhtml/system_config/edit',
            [
                '_current' => ['section', 'website', 'store'],
                '_nosid' => true
            ]
        );
    }

    /**
     * Filter paths that are not defined.
     *
     * @param string $prefix Path prefix
     * @param array $groups Groups data.
     * @param string[] $systemXmlConfig Defined paths.
     * @return array Filtered groups.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function filterPaths(string $prefix, array $groups, array $systemXmlConfig): array
    {
        $flippedXmlConfig = array_flip($systemXmlConfig);
        $filtered = [];
        foreach ($groups as $groupName => $childPaths) {
            //When group accepts arbitrary fields and clones them we allow it
            $group = $this->_configStructure->getElement($prefix .'/' .$groupName);
            if (array_key_exists('clone_fields', $group->getData()) && $group->getData()['clone_fields']) {
                $filtered[$groupName] = $childPaths;
                continue;
            }

            $filtered[$groupName] = ['fields' => [], 'groups' => []];
            //Processing fields
            if (array_key_exists('fields', $childPaths)) {
                foreach ($childPaths['fields'] as $field => $fieldData) {
                    //Constructing config path for the $field
                    $path = $prefix .'/' .$groupName .'/' .$field;
                    $element = $this->_configStructure->getElement($path);
                    if ($element
                        && ($elementData = $element->getData())
                        && array_key_exists('config_path', $elementData)
                    ) {
                        $path = $elementData['config_path'];
                    }
                    //Checking whether it exists in system.xml
                    if (array_key_exists($path, $flippedXmlConfig)) {
                        $filtered[$groupName]['fields'][$field] = $fieldData;
                    }
                }
            }
            //Recursively filtering this group's groups.
            if (array_key_exists('groups', $childPaths) && $childPaths['groups']) {
                $filteredGroups = $this->filterPaths(
                    $prefix .'/' .$groupName,
                    $childPaths['groups'],
                    $systemXmlConfig
                );
                if ($filteredGroups) {
                    $filtered[$groupName]['groups'] = $filteredGroups;
                }
            }

            $filtered[$groupName] = array_filter($filtered[$groupName]);
        }

        return array_filter($filtered);
    }

    /**
     * Filters nodes by checking whether they exist in system.xml.
     *
     * @param array $configData
     * @return array
     */
    private function filterNodes(array $configData): array
    {
        if (!empty($configData['groups'])) {
            $systemXmlPathsFromKeys = array_keys($this->_configStructure->getFieldPaths());
            $systemXmlPathsFromValues = array_reduce(
                array_values($this->_configStructure->getFieldPaths()),
                'array_merge',
                []
            );
            //Full list of paths defined in system.xml
            $systemXmlConfig = array_merge($systemXmlPathsFromKeys, $systemXmlPathsFromValues);

            $configData['groups'] = $this->filterPaths($configData['section'], $configData['groups'], $systemXmlConfig);
        }

        return $configData;
    }
}
