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
            } catch (Exception $e) {
                return false;
            }
        }
    }

    /**
        Verify the extension $name is enabled for \Phlesk\Domain $domain.

        @param String         $target The name of the extension.
        @param \Phlesk\Domain $domain The domain

        @return Bool
     */
    public function isEnabled($target, \Phlesk\Domain $domain)
    {
        $module = \Phlesk\Context::in($target);

        $extension = ucfirst(strtolower($target));

        $permission = (bool)$domain->hasPermission("manage_{$target}");

        return \Phlesk\Context::out($module, $permission);
    }
}
