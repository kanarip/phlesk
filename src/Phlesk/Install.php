<?php

namespace Phlesk;

class Install
{
    public static function isInstalled()
    {
        return static::getInstaller()->isInstalled();
    }

    public static function isInstalling($taskman = null)
    {
        if (!$taskman) {
            $taskman = new \pm_LongTask_Manager();
        }
        $tasks = $taskman->getTasks(['task_install']);
        // Avoid starting multiple install tasks
        foreach ($tasks as $task) {
            $status = $task->getStatus();
            \pm_Log::debug("Found an existing install task with status" . var_export($status, true));
            if ($status == \pm_LongTask_Task::STATUS_RUNNING) {
                \pm_Log::info("Found an existing running task");
                return true;
            }
        }
        return false;
    }

    public static function preInstall()
    {
        static::getInstaller()->preInstall();
    }

    public static function postInstall()
    {
        static::getInstaller()->postInstall();
    }

    public static function preUninstall()
    {
        static::getInstaller()->preUninstall();
    }

    public static function install()
    {
        //Makes use of late static binding
        static::getInstaller()->install();
    }

    public static function startInstallTask($task)
    {
        if (self::isInstalled()) {
            \pm_Log::info("Mattermost is already installed, not installing again.");
            return false;
        }
        $taskman = new \pm_LongTask_Manager();
        if (self::isInstalling($taskman)) {
            \pm_Log::info("Mattermost is currently installing.");
            return false;
        }

        $taskman->start($task);
        return true;
    }

    public static function getInstaller()
    {
        return new Installer();
    }
}
