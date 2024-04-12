# Data

**Data** library provides support to form, data interpreters and some data structures that support database and layout

## Form

**Forms** hold entity data and provide way to render data out. This library provides a list of different type form elements.

## Data Interpreters

**Data interpreter** is responsible for computation of effective value, i.e. evaluation of input data. Each individual interpreter recognizes only one particular type of input data. *Magento\Framework\Data\Argument\Interpreter\Composite* is used to dynamically choose which of underlying interpreters to delegate evaluation to. Child interpreters can be registered in it via constructor and later on through the adder method of its public interface. Each sub-interpreter is associated with a unique name during adding to the composite. In order to make a decision of which interpreter to use, input data has to carry an extra metadata – data key carrying name of an interpreter to use. Metadata value is intended for the composite interpreter only, thus it's not passed down to underlying interpreters. Data interpreters are used for handling DI arguments and layout arguments.

## Supported Data Structures

### Data Collections

**Data Collection** is traversable, countable, ordered list. Class *Magento\Framework\Data\Collection* is at the top of the collections hierarchy. Every collection in the system is its descendant, directly or indirectly.

* Database Data Collections are used to load items from a database. Two fetching strategies are supported in this library:
  * Cache fetching strategy - retrieve data from cache
  * Query fetching strategy - retrieve data from database
* Filesystem Data Collection is used to scan a folder for files and/or folders.
  
### DataArray

**DataArray** is data container with array access.

### Graph

**Graph** is a graph data structure with some basic validation of nodes and search features.

### Tree

**Tree** is a tree data structure. It is used to hold database data.

### Structure

**Structure** is a hierarchical data structure of elements. A structure contains elements; elements can be grouped into groups under the structure. It is used in layout.
