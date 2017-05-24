<?php
/**
 * Build Schema script
 *
 * @package infodbapi
 * @subpackage build
 */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

$script_name = __FILE__;
$root = dirname(dirname(__FILE__)).'/';
require_once $root . '_build/build.config.php';
require_once $root . '_build/includes/functions.php';

include_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');
$modx->loadClass('transport.modPackageBuilder','',false, true);
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

$sources = array(
    'root' => $root,
    'core' => $root.'core/components/' . PKG_NAME_LOWER . '/',
    'model' => $root.'core/components/' . PKG_NAME_LOWER . '/model/',
    'assets' => $root.'assets/components/' . PKG_NAME_LOWER . '/',
    'schema' => $root.'_build/schema/',
);

$manager= $modx->getManager();
$generator= $manager->getGenerator();

/** @var xPDOManager $manager */
$manager = $modx->getManager();
/** @var xPDOGenerator $generator */
$generator = $manager->getGenerator();

// Remove old model
rrmdir($sources['model'] . PKG_NAME_LOWER . '/mysql');

// Generate a new model
$generator->parseSchema($sources['schema'] . PKG_NAME_LOWER . '.mysql.schema.xml', $sources['model']);

$modx->log(modX::LOG_LEVEL_INFO, 'Model generated.');

$mtime= microtime();
$mtime= explode(" ", $mtime);
$mtime= $mtime[1] + $mtime[0];
$tend= $mtime;
$totalTime= ($tend - $tstart);
$totalTime= sprintf("%2.4f s", $totalTime);
echo "\nExecution time: {$totalTime}\n";
exit ();
