<?php
/**
    Extending \pm_Domain.

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
    Extending \pm_Domain

    PHP Version 5

    @category  PHP
    @package   Phlesk
    @author    Jeroen van Meeuwen (Kolab Systems) <vanmeeuwen@kolabsys.com>
    @author    Christian Mollekopf (Kolab Systems) <mollekopf@kolabsys.com>
    @copyright 2019 Kolab Systems AG <contact@kolabsystems.com>
    @license   GPLv3 (https://www.gnu.org/licenses/gpl.txt)
    @link      https://pxts.ch
 */
class Domain extends \pm_Domain
{
    /**
        Disable the integration between the current context and the target module.

        Ergo, if this is called from the 'kolab' module, and it wishes to no longer integrate with
        the 'seafile' module;

        ```php
          $result = $domain->disableIntegration('seafile');
        ```

        @param String $target The module to disable integration with.

        @return Boolean
     */
    public function disableIntegration(String $target)
    {
        $module = \pm_Context::getModuleId();

        if ($target == $module) {
            \pm_Log::err("Can not disable integration with self.");
            return false;
        }

        $extension = ucfirst(strtolower($target));

        $disable_class = "Modules_{$extension}_Domain";
        $disable_function = "disable{$extension}Integration";

        if (!class_exists($disable_class)) {
            \pm_Log::err("Can not disable {$extension}: No class {$disable_class}");
            return false;
        }

        if (!method_exists($disable_class, $disable_method)) {
            \pm_Log::err(
                "Can not disable {$extension}: No method {$disable_class}::{$disable_function}"
            );

            return false;
        }

        $result = call_user_func_array("{$disable_class}::{$disable_function}", [$this]);

        return \Phlesk\Context::out($module, $result);
    }

    /**
        Enable the integration between the current context and the target module.

        @param String $target The module to enable integration with.

        @return Boolean
     */
    public function enableIntegration($target)
    {
        $module = \pm_Context::getModuleId();

        if ($target == $module) {
            \pm_Log::err("Can not enable integration with self.");
            return false;
        }

        $extension = ucfirst(strtolower($target));

        $enable_class = "Modules_{$extension}_Domain";
        $enable_function = "enable{$extension}Integration";

        if (!class_exists($enable_class)) {
            \pm_Log::err("Can not enable {$extension}: No class {$enable_class}");
            return false;
        }

        if (!method_exists($enable_class, $enable_method)) {
            \pm_Log::err(
                "Can not enable {$extension}: No method {$enable_class}::{$enable_function}"
            );

            return false;
        }

        $result = call_user_func_array("{$enable_class}::{$enable_function}", [$this]);

        return \Phlesk\Context::out($module, $result);
    }

    /**
        Retrieve a list of all domains.

        Do not use this function. Instead use \Phlesk::getAllDomains();

        @param Boolean $main    Main domains only.
        @param Boolean $hosting Domains with hosting only.
        @param Boolean $mail    Domains with mail service only.

        @return Array
     */
    public static function getAllDomains($main = false, $hosting = false, $mail = false)
    {
        \pm_Log::warn("Use Phlesk::getAllDomains()");

        return \Phlesk::getAllDomains($main, $hosting, $mail);
    }

    /**
        Override \pm_Domain::getByGuid().

        Do not use this function. Instead use \Phlesk::getDomainByGuid();
        Needed because \pm_Domain::getGuid() will happily log an error rather than simply
        return NULL.

        @param String $domain_guid The GUID.

        @return \Phlesk\Domain|NULL
     */
    public static function getByGuid($domain_guid)
    {
        \pm_Log::warn("Use Phlesk::getDomainByGuid()");

        if ((String)$domain_guid !== $domain_guid) {
            \pm_Log::warn('\Phlesk\Domain parameter \$domain_guid should be a String.');
        }

        return \Phlesk::getDomainByGuid((String)$domain_guid);
    }

    /**
        Override \pm_Domain::getByDomainId().

        Do not use this function. Instead use \Phlesk::getDomainById();
        Needed because \pm_Domain::getByDomainId() will happily log an error rather than simply
        return NULL.

        @param Integer $domain_id The ID.

        @return \Phlesk\Domain|NULL
     */
    public static function getByDomainId($domain_id)
    {
        \pm_Log::warn("Use Phlesk::getDomainById()");

        if ((Integer)$domain_id !== $domain_id) {
            \pm_Log::warn('\Phlesk\Domain parameter \$domain_id should be an Integer.');
        }

        return \Phlesk::getDomainById((Integer)$domain_id);
    }

    /**
        Determine if a domain actually has hosting.

        Needed because \pm_Domain::hasHosting() does not accurately reflect the then-current
        status.

        @return Boolean
     */
    public function hasHosting()
    {
        \pm_Log::debug("Phlesk_Domain->hasHosting() for {$this->getName()} in " . __FILE__);

        return parent::hasHosting();
    }


    /**
        Determine if a domain actually has mail service enabled.

        Needed because the function doesn't exist for \pm_Domain.

        @return Boolean
     */
    public function hasMailService()
    {
        \pm_Log::err("Not yet implemented: Phlesk\Domain->hasMailService()");
        return true;
    }
}
