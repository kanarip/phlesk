<?php
/**
    Provide a stable, consistent and shared means to manage the \pm_Context for extensions, by
    extending \pm_Context.

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
    This class extends \pm_Context to facilitate context switching.

    PHP Version 5

    @category  PHP
    @package   Phlesk
    @author    Jeroen van Meeuwen (Kolab Systems) <vanmeeuwen@kolabsys.com>
    @author    Christian Mollekopf (Kolab Systems) <mollekopf@kolabsys.com>
    @copyright 2019 Kolab Systems AG <contact@kolabsystems.com>
    @license   GPLv3 (https://www.gnu.org/licenses/gpl.txt)
    @link      https://pxts.ch
 */
class Context extends \pm_Context
{
    /**
        Switch the context to target.
        Note this function returns the current context, and you should save it off in order to
        switch back.

        @param String $target The name of the target context.

        @return String The name of the past context (before switching).
     */
    public static function in($target)
    {
        $source = \pm_Context::getModuleId();

        if ($source != $target) {
            \pm_Log::debug("Switching context from {$source} to {$target}");
            //\pm_Log::debug("Resetting context {$source}");
            //\pm_Context::reset();
            \pm_Log::debug("Initializing context {$target}");
            \pm_Context::init($target);
            \pm_Log::debug("Switched context from {$source} to {$target}");
        }

        return $source;
    }

    /**
        Switch the context back to the original ($target), and optionally return $return.

        @param String $target The name of the target context.
        @param Mixed  $return An optional return value or NULL by default.

        @return Mixed The value of the parameter $return.
     */
    public static function out($target, $return = null)
    {
        $source = self::getModuleId();

        if ($source != $target) {
            \pm_Log::debug("Switching context from {$source} to {$target}");
            //\pm_Log::debug("Resetting context {$source}");
            //\pm_Context::reset();
            \pm_Log::debug("Initializing context {$target}");
            \pm_Context::init($target);
            \pm_Log::debug("Switched context from {$source} to {$target}");
        }

        return $return;
    }
}
