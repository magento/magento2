Library provides Unserialize class that has custom unserialize method that checks if serialized string contains
serialized object and do not unserialize it. If string doesn't contain object, method calls native PHP function
unserialize.