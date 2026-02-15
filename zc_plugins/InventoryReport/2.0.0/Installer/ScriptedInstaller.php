<?php

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        zen_deregister_admin_pages(['inventory_report']);
        zen_register_admin_page('inventory_report', 'BOX_REPORTS_INVENTORY_REPORT', 'FILENAME_STATS_INVENTORY_REPORT', '', 'reports', 'Y', 17);

    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(['inventory_report']);

    }
}
