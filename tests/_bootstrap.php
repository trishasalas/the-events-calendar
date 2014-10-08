<?php
// This is global bootstrap for autoloading
include dirname( dirname( __FILE__ ) ) . '/vendor/autoload.php';
$kernel = \AspectMock\Kernel::getInstance();
$kernel->init( [
	'debug'        => true,
	'includePaths' => [ __DIR__ . '/../lib/tickets' ],
	'excludePaths' => [ __DIR__, __DIR__ . '/../vendor' ]
] );
