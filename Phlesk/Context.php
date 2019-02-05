<?php
    namespace Phlesk;

    /**
        This class extends \pm_Context to facilitate context switching.
    */
    class Context extends \pm_Context {
        public static function in($target) {
           $source = self::getModuleId();

           if ($source != $target) {
                self::reset();
                self::init($target);
                \pm_Log::debug("Switched context from {$source} to {$target}");
            }

            return $source;
        }

        public static function out($target, $return = NULL) {
            $source = self::getModuleId();

            if ($source != $target) {
                \pm_Log::debug("Switching context from {$source} to {$target}");
                self::reset();
                self::init($target);
            }

            return $return;
        }
    }
