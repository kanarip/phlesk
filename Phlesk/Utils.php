<?php
// phpcs:ignore
namespace Phlesk;

// phpcs:ignore
class Utils
{
    /**
        Download the extension's application release file from the interwebz.

        @return Bool
     */
    public static function downloadRelease()
    {
        self::_waitForCompleteInstallation();

        $base_url = "https://mirror.kolabenterprise.com/pub/releases/";

        $module = \Phlesk\Context::getModuleId();
        $extension = ucfirst(strtolower($module));

        $version_class = "Modules_{$extension}_Version";

        if (!class_exists($version_class)) {
            \pm_Log::err("No version class exists for {$extension}");
            return false;
        }

        $filename = $version_class::getFilename();

        $url = $base_url . $filename;

        $var_dir = rtrim(\Phlesk\Context::getVarDir(), '/');

        $target_file = "{$var_dir}/{$filename}";
        $temp_file = tempnam($var_dir, $filename);

        $fm = new \pm_ServerFileManager();

        if ($fm->fileExists($target_file)) {
            return true;
        }

        $result = \Phlesk::exec(
            [
                "wget",
                "-O{$temp_file}",
                $url
            ],
            true
        );

        if ($result['code'] != 0) {
            \pm_Log::err("Failed to download {$url}: {$result['stderr']}");
            return false;
        }

        if (!$fm->fileExists($target_file)) {
            $result = \Phlesk::exec(["mv", $temp_file, $target_file]);

            if ($result['code'] != 0) {
                \pm_Log::err("Failed to download {$url}: {$result['stderr']}");
                return false;
            }
        }

        return true;
    }

    /**
        Initialize the default permissions for an extension.

        @return NULL
     */
    public static function initDefaultPermission()
    {
        if (!self::_canManagePlans()) {
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

    /**
        Wait for any post-install jobs to have actually completed.

        @return NULL
     */
    private static function _waitForCompleteInstallation()
    {
        $post_installing = \pm_Settings::get('installing', null) == "true";

        // Useless. Join the query below.
        /*
        $module = new \pm_Extension("seafile");
        $module_id = $module->getId();
        */

        $db = \pm_Bootstrap::getDbAdapter();

        while ($post_installing) {
            \pm_Log::debug("Seafile extension is not yet completely installed ...");
            sleep(3);

            /**
                TODO:

                Raise with plesk the fact that a \pm_Settings::get('installing') won't update
                itself even though it can be shown the underlying data changes.

                // Doesn't work:

                $post_installing = \pm_Settings::get('installing') == "true";

                // Doesn't work:

                \pm_Context::reset();
                \pm_Context::init("seafile");

                $post_installing = \pm_Settings::get('installing') == "true";

                // Doesn't work either:

                $settings = new \pm_Settings();
                $post_installing = $settings->get('installing') == "true";
                unset $settings;

                // Verify the underlying data with (see output in logs):

                \Phlesk::exec(
                    [
                        'plesk',
                        'db',
                        '-e',
                        'SELECT value FROM ModuleSettings WHERE name = "installing";'
                    ]
                );
            */

            $result = $db->query(
                sprintf(
                    "SELECT ms.value FROM ModuleSettings ms
                        INNER JOIN Modules m ON m.id = ms.module_id
                        WHERE m.name = '%s' AND
                            ms.name = 'installing' AND
                            ms.value = 'false'",
                    \Phlesk\Context::getModuleId()
                )
            );

            if ($result->rowCount() > 0) {
                $post_installing = false;
            }
        }
    }
}
