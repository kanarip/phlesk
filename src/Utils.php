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
            "phlesk-execute",
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

    /**
     * Download the file into $target_directory
     *
     * This routine ensures the file appears atomically,
     * by downloading to a temporary file in $target_directory and renaming at the end.
     *
     * @return TRUE if the tarball is available, FALSE if not
     */
    public static function downloadFile($url, $target_directory, $file_name, \pm_ServerFileManager $fm)
    {
        $target_dir = rtrim($target_directory, '/');

        $tar_file = "{$target_dir}/{$file_name}";
        $tmp_file = tempnam($target_dir, $file_name);

        if ($fm->fileExists($tar_file)) {
            return true;
        }

        \pm_Log::debug("Downloading {$tar_file}");

        // Download to temp directory and then move, for it to appear atomic
        $result = self::exec(["wget", "-O{$tmp_file}", "{$url}"], true);

        // This could also fail because there is no connection to the internet, so not necessarily an error.
        if ($result['code'] != 0) {
            \pm_Log::info(
                "Failed to download {$file_name}: '" . $result['stderr']
            );

            return false;
        }

        // We check again in case the file has been downloaded by someone else meanwhile
        if (!$fm->fileExists($tar_file)) {
            $result = self::exec(["mv", "{$tmp_file}", "{$tar_file}"]);
            if ($result['code'] != 0) {
                return false;
            }
        }

        return true;
    }

    /**
        Switch to the $extension context.

        @return String The name of the current module.
     */
    public static function contextIn($extension)
    {
        $module = \pm_Context::getModuleId();

        if ($module != $extension) {
            \pm_Context::reset();
            \pm_Context::init($extension);
            \pm_Log::debug("Switched context from {$module} to {$extension}");
        }

        return $module;
    }

    /**
        Switch out of our context, back to the previous (if there was any).

        @return Mixed $return
     */
    public static function contextOut($extension, $previous, $return)
    {
        if ($previous != $extension) {
            \pm_Log::debug("Switching context from {$extension} to {$module}");
            \pm_Context::reset();
            \pm_Context::init($previous);
        }

        return $return;
    }
}
