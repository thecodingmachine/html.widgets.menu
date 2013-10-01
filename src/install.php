<?php
use Mouf\Html\Utils\WebLibraryManager\WebLibraryInstaller;

require_once __DIR__."/../../../autoload.php";

use Mouf\MoufManager;

use Mouf\Actions\InstallUtils;
use Mouf\Html\Renderer\RendererUtils;

// Let's init Mouf
InstallUtils::init(InstallUtils::$INIT_APP);

// Let's create the instance
$moufManager = MoufManager::getMoufManager();

RendererUtils::createPackageRenderer($moufManager, "mouf/html.widgets.menu");

// Let's rewrite the MoufComponents.php file to save the component
$moufManager->rewriteMouf();

// Finally, let's continue the install
InstallUtils::continueInstall(isset($_REQUEST['selfedit']) && $_REQUEST['selfedit'] == 'true');
?>