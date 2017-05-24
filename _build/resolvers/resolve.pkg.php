<?php

if ($object->xpdo) {
	/** @var modX $modx */
	$modx =& $object->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
			$modelPath = $modx->getOption('infodbapi.core_path', null, $modx->getOption('core_path') . 'components/infodbapi/') . 'model/';

			$modx->addPackage('infodbapi', $modelPath);
        	$modx->addExtensionPackage('infodbapi', $modelPath);

			$manager = $modx->getManager();

			break;

		case xPDOTransport::ACTION_UPGRADE:
			break;

		case xPDOTransport::ACTION_UNINSTALL:
    		    if ($modx instanceof modX) {
    		        $modx->removeExtensionPackage('infodbapi');
    		    }
		    break;
	}
}
return true;
