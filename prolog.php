<?

use Revo\Helpers\Extensions;

$extension = new Extensions();
$moduleID = $extension->getModuleID();

define('ADMIN_MODULE_NAME', $moduleID);
