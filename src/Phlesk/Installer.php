<?php

namespace Phlesk;

class Installer
{
    /**
        Explicitly invoked if not already installed
     */
    public function install()
    {
    }

    public function isInstalled()
    {
        return true;
    }

    /**
        Automatically invoked on before extension installation
     */
    public function preInstall()
    {
    }

    public function postInstall()
    {
    }

    public function preUninstall()
    {
    }
}
