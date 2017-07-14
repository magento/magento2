# EntityManager

**EntityManager** library contains functionality for management entity instances.
It includes read and write operations as for entity as for their attributes and extensions.

It's not recommended to use EntityManager and its infrastructure for your entity persistence.
In the nearest future new Persistence Entity Manager would be released which will cover all the requirements for 
persistence layer along with Query API as performance efficient APIs for Read scenarios.

Currently, it's recommended to use Resource Model infrastructure and make a successor of 
Magento\Framework\Model\ResourceModel\Db\AbstractDb class.

For filtering operations, it's recommended to use successor of 
Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
