A library for multi dimensional indexing processing

it's supposed to split indexes by the Dimensional (Scope). Builds independent index table per each Dimensional. 
Dimension and Alias objects help to resolve correct index.

Contains switchable indexes to switch from an old index (current index, which exists before full reindex operation 
has been launched) to a new index (created when the full reindex operation finished) with zero downtime to make 
Front-End responsive while Full Reindex being worked.
