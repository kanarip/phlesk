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

        return true;
    }

    /**
        Verify the extension $name is enabled for \Phlesk\Domain $domain.

        @param String         $target The name of the extension.
        @param \Phlesk\Domain $domain The domain

        @return Bool
     */
    public static function isEnabled($target, \Phlesk\Domain $domain)
    {
        if (!self::isActive($target)) {
            return false;
        }

        if (!self::isInstalled(\Phlesk\Context::getModuleId())) {
            return false;
        }

        $module = \Phlesk\Context::in($target);

        if (!self::isInstalled($target)) {
            return false;
        }

        $extension = ucfirst(strtolower($target));

        $permission = (bool)$domain->hasPermission("manage_{$target}");

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

        $extension_installed_func = "Modules_{$extension}_Install::isInstalled";

        if (method_exists("Modules_{$extension}_Install", "isInstalled")) {
            return $extension_installed_func();
        }

        return true;
    }
}
