<?php

if ($object->xpdo) {
	/** @var modX $modx */
	$modx =& $object->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
			$modelPath = $modx->getOption('infodbfiles.core_path', null, $modx->getOption('core_path') . 'components/infodbfiles/') . 'model/';

			$modx->addPackage('infodbfiles', $modelPath);
        		$modx->addExtensionPackage('infodbfiles', $modelPath);

			$manager = $modx->getManager();

			break;

		case xPDOTransport::ACTION_UPGRADE:
			break;

		case xPDOTransport::ACTION_UNINSTALL:
    		    if ($modx instanceof modX) {
    		        $modx->removeExtensionPackage('infodbfiles');
    		    }
		    break;
	}
}
return true;