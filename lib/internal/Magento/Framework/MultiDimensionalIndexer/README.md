MultiDimensionalIndexer
=======
The **\Magento\Framework\MultiDimensionalIndexer** library provides functionality of multi-dimension index creation and
handling.

Library introduces a set of extension points which split monolithic index by specified Dimension (Scope), creating 
independent index (i.e. dedicated MySQL table) per each Dimension. Along with that library provides index name 
resolving mechanism based on provided scope. The Multi-Dimension indexes introduced for the sake of data scalability
and ability to reindex data in the scope of particular Dimension only.

Aliasing mechanism guarantees zero downtime to make Front-End responsive while Full Reindex being processed.
