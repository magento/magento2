# EntityManager

**EntityManager** library contains functionality for entity persistence layer.
EntityManager supports persistence of basic entity attributes as well as extension and custom attributes 
added by 3rd party developers for the purpose of extending default entity behavior.

It's not recommended to use EntityManager and its infrastructure for your entity persistence.
In the nearest future new Persistence Entity Manager would be released which will cover all the requirements for 
persistence layer along with Query API as performance efficient APIs for Read scenarios.

Currently, it's recommended to use Resource Model infrastructure and make a successor of 
Magento\Framework\Model\ResourceModel\Db\AbstractDb class or successor of 
Magento\Eav\Model\Entity\AbstractEntity if EAV attributes support needed.

For filtering operations, it's recommended to use successor of 
Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection class.
