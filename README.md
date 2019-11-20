List of components:
* ArrayHelper - imported from Yii framework, allow to reindex/group arrays values
* ConfigBase - object for config
* ConfigHelper - read/write array config
* CurlHelper - convenient wrapper for curl and some methods
* ErrorHelper - allow to log debug info (traceroute) for functions
* FileHelper - allow to find files, read a text file tail, write/read with locking. You can use any more interest libraries like symfony's filesystem and finder
* IpHelper - allow to check if IP address is part of subnets. Usable for detect concrete provider for example
* RequestHelper - $_SERVER globals handlers
* RetryHelper - allow to looping running a code if it fails
* StreamHelper - now 1 method, and not best variant: for get status code of URL with sockets
* SystemHelper - just to get OS name
* UserAgentHelper - allow to retrieve random real User-Agent string
* VersionHelper - for increase/decrease semantic versions (using third SemVer library)

Short helpers included as separate functions:
* env() - get environment value
* head() / last() - array access aliases
* retry() - short retry code version wit goto operator
* include_from() - include scope
* is_console() - detect if CLI application now
* pre_r() - wrap to PRE tag for clean printing