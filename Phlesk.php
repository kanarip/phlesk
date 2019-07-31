<?php
/**
    Welcome to Phlesk.

    Hope you enjoy ;-)

    PHP Version 5

    @category  PHP
    @package   Phlesk
    @author    Jeroen van Meeuwen (Kolab Systems) <vanmeeuwen@kolabsys.com>
    @author    Christian Mollekopf (Kolab Systems) <mollekopf@kolabsys.com>
    @copyright 2019 Kolab Systems AG <contact@kolabsystems.com>
    @license   GPLv3 (https://www.gnu.org/licenses/gpl.txt)
    @link      https://pxts.ch
 */
// phpcs:ignore
class Phlesk
{
    public const VERSION = '0.1';

    /**
        Switch the context from the current \pm_Context to the target context.

        Note it only switches context if necessary.

        Use this to ensure extensions calling functions of one another do not incidentally
        operate in the incorrect context.

        Note: It should be considered the responsibility of the target extension to ensure the
        context in which it operates is the correct context, and at or near the end of the
        function, it is also responsible for switching the context back to the original.

        @param String $target The string representation of the target context, i.e. "kolab",
                              "seafile", etc.

        @return String The name of the current context.
     */
    public static function contextIn($target)
    {
        return \Phlesk\Context::in($target);
    }

    /**
        Switch out of the current \pm_Context back to an original context.
        Note it only switches context if necessary.

        Use in conjunction with \Phlesk::contextIn() which returns a string representing the
        original context:

        ```php
          function foo() {
              $module = \Phlesk::contextIn("mymodule");
              // (... do work in the mymodule context ...)
              \Phlesk::contextOut($module);
          }
        ```

        You may specify an intended return value, such that you can reduce the code footprint:

        ```php
          function foo() {
              $module = \Phlesk::contextIn("mymodule");
              // (... do work in the mymodule context ...)
              $result = $retval >= 1 ? FALSE : TRUE;
              return \Phlesk::contextOut($module, $result);
          }
        ```

        @param String $target The string representation of the target context, hopefully the
                              correct one to switch back to after your work is done.
        @param Mixed  $return Return this value after switching contexts.

        @return Mixed Returns the value of $return.
     */
    public static function contextOut($target, $return = null)
    {
        return \Phlesk\Context::out($target, $return);
    }

    /**
        Execute a command with error control.

        @param Array            $command  The command and arguments to execute.
        @param Bool             $tolerant Whether or not the failure of execution is fatal
                                          (default).
        @param pm_LongTask_Task $task     The long-running task that is being executed, if any.

        @return Array Result of command execution, including 'code', 'stderr', 'stdout'.
     */
    public static function exec(
        Array $command,
        Bool $tolerant = false,
        pm_LongTask_Task $task = null
    ) {

        $module = \Phlesk\Context::getModuleId();

        $result = \pm_ApiCli::callSbin("{$module}-execute", $command, \pm_ApiCli::RESULT_FULL);

        if ($result['code'] != 0 && !$tolerant) {
            \pm_Log::err("Error executing: ;" . implode(' ', $command) . "'");
            \pm_Log::err("stderr: " . $result['stderr']);
        }

        return $result;
    }

    /**
        Obtain a list of domains.

        @param Bool $main           Only return domains that are primary domains for a subscription.
        @param Bool $hosting        Only return domains that have hosting enabled.
        @param Bool $mail           Only return domains that have mail service enabled.
        @param Func $filter_methods An optional function to apply as a filter.

        @return Array Returns a list of \Phlesk\Domain objects.
     */
    public static function getAllDomains(
        $main = false,
        $hosting = false,
        $mail = false,
        $filter_methods = []
    ) {
        $client = null;
        $domains = [];
        $pm_domains = [];

        $module = \Phlesk\Context::getModuleId();
        $extension = ucfirst(strtolower($module));

        $filter_class = "Modules_{$extension}_Utils";

        $session = \pm_Session::isExist();

        if ($session) {
            $client = \pm_Session::getClient();
        }

        if ($client == null) {
            $pm_domains = \pm_Domain::getAllDomains($main);
        } elseif ($client->isAdmin()) {
            $pm_domains = \pm_Domain::getAllDomains($main);
        } elseif ($client->isReseller()) {
            $all_domains = \pm_Domain::getAllDomains($main);

            foreach ($all_domains as $domain) {
                if ($client->hasAccessToDomain($domain->getId())) {
                    $pm_domains[] = $domain;
                }
            }
        } else {
            $pm_domains = \Phlesk::getDomainsByClient($client);
        }

        foreach ($pm_domains as $pm_domain) {
            $domain = new \Phlesk\Domain($pm_domain->getId());

            if ($hosting && !$domain->hasHosting()) {
                continue;
            }

            if ($mail && !$domain->hasMailService()) {
                continue;
            }

            $skip = false;

            if ($filter_methods) {
                foreach ($filter_methods as $filter_method) {
                    if (method_exists($filter_class, $filter_method)) {
                        $method = "{$filter_class}::{$filter_method}";

                        if ($result = call_user_func_array($method, [$domain])) {
                            $skip = true;
                        }
                    }
                }
            }

            if ($skip) {
                continue;
            }

            $domains[] = $domain;
        }

        return $domains;
    }

    /**
        Get a \Phlesk\Domain using its GUID.

        @param String $domain_guid The GUID of the domain to find and return.

        @return \Phlesk\Domain|NULL
     */
    public static function getDomainByGuid(String $domain_guid)
    {
        // Must use \pm_Domain to avoid loops
        $domains = \pm_Domain::getAllDomains();

        foreach ($domains as $domain) {
            if ($domain->getGuid() == $domain_guid) {
                return new \Phlesk\Domain($domain->getId());
            }
        }

        return null;
    }

    /**
        Get a \Phlesk\Domain by its numeric identifier.  Really, you could just use:

        ```php
           $domain = new \Phlesk\Domain($domain_id);
        ```

        @param Int $domain_id The ID of the domain to return.

        @return \Phlesk\Domain|NULL
     */
    public static function getDomainById(Int $domain_id)
    {
        // Must use \pm_Domain to avoid loops
        $domains = \pm_Domain::getAllDomains();

        foreach ($domains as $domain) {
            if ($domain->getId() == $domain_id) {
                return new \Phlesk\Domain($domain_id);
            }
        }

        return null;
    }

    /**
        Get a \Phlesk\Domain by its name.

        @param String $domain_name The name of the domain to return.

        @return \Phlesk\Domain|NULL
     */
    public static function getDomainByName(String $domain_name)
    {
        // Must use \pm_Domain to avoid loops
        $domains = \pm_Domain::getAllDomains();

        foreach ($domains as $domain) {
            if ($domain->getName() == $domain_name) {
                return new \Phlesk\Domain($domain->getId());
            }
        }

        return null;
    }

    /**
        Get a name for a \Phlesk\Domain by its ID.

        @param Int $domain_id The ID for the domain to obtain the name for.

        @return String
     */
    public static function getDomainNameByID(Int $domain_id)
    {
        $domain = \Phlesk::getDomainById($domain_id);
        return $domain->getName();
    }

    /**
        Get domains for a client.

        @param \pm_Client $client   The pm_Client to return domains for.
        @param Bool       $mainOnly Only return main domains, not sub-domains, nor aliases.

        @return Array A list with \Phlesk\Domain items.
     */
    public static function getDomainsByClient(\pm_Client $client, $mainOnly = false)
    {
        $domains = array();

        $pm_domains = \pm_Domain::getDomainsByClient($client, $mainOnly);

        foreach ($pm_domains as $pm_domain) {
            $domains[] = new \Phlesk\Domain($pm_domain->getId());
        }

        return $domains;
    }

    /**
        Get the primary domain for any domain.

        Basically, maybe, the primary subscription domain. Terminology is confusing.

        @param String $domain_guid The GUID for the domain to retain the primary domain for.

        @return \Phlesk\Domain|NULL
     */
    public static function getPrimaryDomain(String $domain_guid)
    {
        $domains = \Phlesk::getAllDomains(true);

        foreach ($domains as $domain) {
            if ($domain->getGuid() == $domain_guid) {
                return $domain;
            }
        }

        return null;
    }

    /**
        Confirm or deny the domain in question is the primary domain.

        @param String $domain_guid The GUID for the domain to confirm/deny its primacy of.

        @return Bool
     */
    public static function isPrimaryDomain(String $domain_guid)
    {
        $domains = \Phlesk::getAllDomains(true);

        foreach ($domains as $domain) {
            if ($domain->getGuid() == $domain_guid) {
                return true;
            }
        }

        return false;
    }
}
