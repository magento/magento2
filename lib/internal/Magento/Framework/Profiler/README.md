A library for profiling source code. This is a manual type of profiler, when programmer adds profiling instructions explicitly inline to the source code.

Features:

 * Measures time between tags (events), number of calls and calculates average time
 * Measures memory usage
 * Allows nesting of events and enforces its integrity, and measures aggregated stats of nested elements
 * Allows configuring filters for tags
 * Provides various output formats out of the box: direct HTML output and CSV-file
