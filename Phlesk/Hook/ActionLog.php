<?php
/**
    Provide a standardized extension of \pm_ActionLog

    PHP Version 5

    @category  PHP
    @package   Modules_Seafile
    @author    Jeroen van Meeuwen (Kolab Systems) <vanmeeuwen@kolabsys.com>
    @author    Christian Mollekopf (Kolab Systems) <mollekopf@kolabsys.com>
    @copyright 2019 Kolab Systems AG <contact@kolabsystems.com>
    @license   GPLv3 (https://www.gnu.org/licenses/gpl.txt)
    @link      https://pxts.ch
 */
namespace Phlesk\Hook;

// phpcs:ignore
class ActionLog extends \pm_Hook_ActionLog
{
    /**
        Disclose the events we're planning on submitting.

        @return Array
     */
    public function getEvents()
    {
        return [
            'enable_domain' => 'Enable Integration for Domain',
            'disable_domain' => 'Disable Integration for Domain'
        ];
    }
}
