<?php
    /**
        Provide a stable, consistent and shared means to manage the \pm_Context for extensions, by
        extending \pm_Context.
    */
    namespace Phlesk;

    /**
        This class extends \pm_Context to facilitate context switching.
    */
    class Context extends \pm_Context {
        /**
            Switch the context to target.

            Note this function returns the current context, and you should save it off in order to
            switch back.

            @param String $target   The name of the target context.

            @return String  The name of the past context (before switching).
        */
        public static function in($target) {
           $source = self::getModuleId();

           if ($source != $target) {
                self::reset();
                self::init($target);
                \pm_Log::debug("Switched context from {$source} to {$target}");
            }

            return $source;
        }

        /**
            Switch the context back to the original ($target), and optionally return $return.

            @param String $target   The name of the target context.
            @param mixed $return    An optional return value or NULL by default.

            @return mixed   The value of the parameter $return.
        */
        public static function out($target, &$return = NULL) {
            $source = self::getModuleId();

            if ($source != $target) {
                \pm_Log::debug("Switching context from {$source} to {$target}");
                self::reset();
                self::init($target);
            }

            return $return;
        }
    }
