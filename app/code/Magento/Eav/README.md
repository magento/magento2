# Magento_Eav module

Magento_Eav stands for Entity-Attribute-Value. The purpose of the Magento_Eav module is to make entities
configurable extendable by an admin user.

## Installation details

The Magento_Config module is one of the base Magento 2 modules. You cannot disable or uninstall this module.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Eav module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Eav module.

### Events

The module dispatches the following events:

- `adminhtml_block_eav_attribute_edit_form_init` event in the `\Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain::_initFormValues` method. Parameters:
    - `form` - form data object(`\Magento\Framework\Data\Form` class).
- `eav_collection_abstract_load_before` event in the `\Magento\Eav\Model\Entity\Collection\AbstractCollection::load` method. Parameters:
    - `collection` is a `$this` object(`\Magento\Eav\Model\Entity\Collection\AbstractCollection` class).

### Public APIs

`\Magento\Eav\Api\AttributeGroupRepositoryInterface`:
- save attribute group.
- retrieve attribute group.
- retrieve a list of attribute groups.
- remove attribute group by an ID.
- remove attribute group.

`\Magento\Eav\Api\AttributeManagementInterface`:
- assign an attribute to the attribute set.
- remove an attribute from the attribute set.
- retrieve related attributes based on given attribute set ID.

`\Magento\Eav\Api\AttributeOptionManagementInterface`:
- add an option to attribute.
- delete option from the attribute.
- retrieve a list of attribute options.

`\Magento\Eav\Api\AttributeOptionUpdateInterface`:
- update attribute option.

`\Magento\Eav\Api\AttributeRepositoryInterface`:
- retrieve all attributes for the entity type.
- retrieve specific attributes.
- create/update attribute data.
- delete an attribute.
- delete attribute by ID.

`\Magento\Eav\Api\AttributeSetManagementInterface`:
- create attribute set from data.

`\Magento\Eav\Api\AttributeSetRepositoryInterface`:
- retrieve a list of attribute sets.
- retrieve attribute set information based on the given ID.
- save/update attribute set data.
- remove attribute set by the given ID.
- remove given attribute set.

## Additional information

For information about significant changes in patch releases, see [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html).
