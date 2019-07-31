<?php
// phpcs:ignore
namespace Phlesk;

// phpcs:ignore
class Utils
{
    /**
        Initialize the default permissions for an extension.

        @return NULL
     */
    public static function initDefaultPermission()
    {
        if (self::_canManagePlans()) {
            \pm_Settings::set('permission-default', 1);
        } else {
            $domains = \Phlesk::getAllDomains(true);

            if (count($domains) == 0) {
                \pm_Settings::set('permission-default', 1);
            } else {
                \pm_Settings::set('permission-default', 0);
            }
        }
    }

    /**
        Determine whether or not this instance of Plesk is able to manage
        hosting plans and reseller plans.

        Without either of those, any permission could no longer be toggled.

        @return Bool
     */
    private static function _canManagePlans()
    {
        $license = new \pm_License();
        $properties = $license->getProperties();

        $hasHostingPlans = $properties['can-manage-customers'];
        $hasResellerPlans = $properties['can-manage-resellers'];

        if (!$hasHostingPlans || !$hasResellerPlans) {
            return false;
        } else {
            return true;
        }
    }
}
