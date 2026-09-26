<?php

/**
 * Front controller when the domain document root is the Laravel project root
 * (common on cPanel). Without this file, Apache serves "Index of /" for "/"
 * and never boots Laravel — so storage/logs stays empty.
 *
 * Preferred long-term setup: point the domain document root at /public instead.
 */
require __DIR__.'/public/index.php';
