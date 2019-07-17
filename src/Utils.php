<?php

namespace Phlesk;

/**
    Static utility functions mostly.
 */
class Utils
{
    public static function getSubscriptionDomains(\pm_Domain $domain)
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

    public static function exec(Array $command, $tolerant = false)
    {
        \pm_Log::debug("phlesk-execute: " . var_export($command, true));

        $result = \pm_ApiCli::callSbin(
            "mattermost-execute",
            $command,
            \pm_ApiCli::RESULT_FULL
        );

        if ($result['code'] != 0 && !$tolerant) {
            \pm_Log::err("Not successfully executed: " . var_export($command, true));
            \pm_Log::err("stderr: " . $result['stderr']);
        }

        return $result;
    }

    public static function mkdirs(Array $directories, \pm_ServerFileManager $fm)
    {
        foreach ($directories as $d) {
            if (!$fm->fileExists($d)) {
                $fm->mkdir($d, '0750', true);
            }
        }
    }

    public static function renderTemplate($template, $target, $substitions, \pm_ServerFileManager $fm)
    {
        $tpl = $fm->fileGetContents($template);
        $result = str_replace(
            array_keys($substitions),
            array_values($substitions),
            $tpl
        );
        $fm->filePutContents($target, $result);
    }
}
