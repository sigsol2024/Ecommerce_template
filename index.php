<?php

/**
 * cPanel / docroot-at-project-root front controller.
 *
 * Visiting "/" maps to this directory. If .htaccess rewrite to public/ fails or is
 * ignored, Apache falls through to DirectoryIndex — without this file it shows
 * "Index of /" while paths like /shop still rewrite correctly into public/.
 */
require __DIR__.'/public/index.php';
