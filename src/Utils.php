<?php

namespace Phlesk;

/**
    Static utility functions mostly.
 */
class Utils
{
    public static function getSubscriptionDomains(pm_Domain $domain)
    {
        // No way up to the subscription, go through client
        $client = $domain->getClient();
        $homepath = $domain->getHomePath();

        $subscription_domains = [];
        $domains = \pm_Domain::getDomainsByClient($client);
        foreach ($domains as $d) {
            if ($d->getHomePath() == $homepath) {
                $subscription_domains[] = $d;
            }
        }

        return $subscription_domains;
    }

    private static function isSubscriptionManagementAvailable()
    {
        // Determine the Plesk edition -- or rather whether subscription
        // management is available.
        $properties = (new \pm_License())->getProperties();
        if (!$properties['can-manage-customers'] || !$properties['can-manage-resellers']) {
            return false;
        }
        return true;
    }

    /**
     * Set the permission default value depending plesk edition.
     */
    public static function initPermissionDefault()
    {
        if (!self::isSubscriptionManagementAvailable()) {
            \pm_Settings::set('permission-default', 1);
        } else {
            // Without any domains, we can set the permission systems' default
            // to TRUE. See T27844.
            $perm_config = \pm_Settings::get('permissions-default', null);

            // If permissions were not set
            if ($perm_config === null) {
                $domains = \pm_Domain::getAllDomains(true);

                if (count($domains) == 0) {
                    \pm_Settings::set('permission-default', 1);
                } else {
                    \pm_Settings::set('permission-default', 0);
                }
            } else {
                \pm_Settings::set('permission-default', $perm_config);
            }
        }

        // Remove legacy permissions-default
        \pm_Settings::set('permissions-default', null);
    }
}
