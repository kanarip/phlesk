<?php
/**
    Extending \pm_Domain.

    PHP Version 5

    @category  PHP
    @package   Phlesk
    @author    Jeroen van Meeuwen (Kolab Systems) <vanmeeuwen@kolabsys.com>
    @author    Christian Mollekopf (Kolab Systems) <mollekopf@kolabsys.com>
    @copyright 2019 Kolab Systems AG <contact@kolabsystems.com>
    @license   GPLv3 (https://www.gnu.org/licenses/gpl.txt)
    @link      https://pxts.ch
 */
namespace Phlesk;

/**
    Extending \pm_Domain

    PHP Version 5

    @category  PHP
    @package   Phlesk
    @author    Jeroen van Meeuwen (Kolab Systems) <vanmeeuwen@kolabsys.com>
    @author    Christian Mollekopf (Kolab Systems) <mollekopf@kolabsys.com>
    @copyright 2019 Kolab Systems AG <contact@kolabsystems.com>
    @license   GPLv3 (https://www.gnu.org/licenses/gpl.txt)
    @link      https://pxts.ch
 */
class Extension
{
    /**
        Verify an extension is active.

        @param String $target The name of the extension to check.

        @return Bool|NULL
     */
    public static function isActive($target)
    {
        if (class_exists('pm_Extension')) {
            try {
                $extension = \pm_Extension::getById(strtolower($target));
                return $extension->isActive();
            } catch (\pm_Exception $e) {
                return false;
            }
        }

        // All extensions have their local utilities class.
        $extension = ucfirst(strtolower($target));

        if (!class_exists("Modules_{$extension}_Utils")) {
            return false;
        }

        return true;
    }

    /**
        Verify the extension $target is enabled for \pm_Domain $domain.

        This includes verifying the extension $target is active, that the source extension is
        installed, verifying the target extension has its software installed, and that the target
        extension permission is enabled.

        @param String     $target The name of the extension.
        @param \pm_Domain $domain The domain

        @return Bool
     */
    public static function isEnabled($target, \pm_Domain $domain)
    {
        if (!self::isActive($target)) {
            \pm_Log::debug("Extension {$target} is not active or not available.");
            return false;
        }

        if (!self::isInstalled(\Phlesk\Context::getModuleId())) {
            \pm_Log::debug(
                sprintf(
                    "Extension %s does not have its software installed.",
                    \Phlesk\Context::getModuleId()
                )
            );

            return false;
        }

        $module = \Phlesk\Context::in($target);

        if (!self::isInstalled($target)) {
            \pm_Log::debug("Extension {$target} does not have its software installed.");
            return false;
        }

        $extension = ucfirst(strtolower($target));

        $permission = false;

        if (class_exists("Modules_{$extension}_Permissions")) {
            if (is_callable(["Modules_{$extension}_Permissions", "getPermissions"], false, $c)) {
                $permissions = $c();
                foreach ($permissions as $_permission => $attrs) {
                    if (!$permission) {
                        $permission = (bool)$domain->hasPermission($_permission);
                    }
                }
            }
        }

        return \Phlesk\Context::out($module, $permission);
    }

    /**
        Verify the extension has installed its software.

        @param String $target The name of the extension to check.

        @return Bool
     */
    public static function isInstalled($target)
    {
        $extension = ucfirst(strtolower($target));

        if (!self::isActive($target)) {
            return false;
        }

        // requires software installation
        $extInstallClass = "Modules_{$extension}_Install";

        if (class_exists($extInstallClass)) {
            $instance = $extInstallClass::getInstance();

            if (method_exists($instance, "isInstalled")) {
                return $instance::isInstalled();
            }
        }
    }
}
