Cliph - a library for testable CLI applications
===============================================

```php
<?php

$options = new \Cliph\Options($argv,array(
  '--help,-h','--connect=NULL','--init=',':cmd'
  ));

// show help
if($options->has('-h','--help') || $options->errors())
{
    $options->printErrors();

    echo "\nusage: $argv[0] [..args] [cmd]\n\n";
    echo " --connect <broker>    : connect to broker, instead of localhost\n";
    echo " --init <cmd>          : command to run if cwd doesn't exist and needs initialization\n";
    echo " --help -h             : this documentation\n";
    echo "\n";
    exit(1);
}

echo $options->value('--connect');

```

Copyright
---------

Copyright (c) 2012 99designs See [LICENSE](https://github.com/99designs/cliph/blob/master/LICENSE) for details.

