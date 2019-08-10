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

/**
    Class documentation.

    PHP Version 5

    @category  PHP
    @package   Phlesk
    @author    Jeroen van Meeuwen (Kolab Systems) <vanmeeuwen@kolabsys.com>
    @author    Christian Mollekopf (Kolab Systems) <mollekopf@kolabsys.com>
    @copyright 2019 Kolab Systems AG <contact@kolabsystems.com>
    @license   GPLv3 (https://www.gnu.org/licenses/gpl.txt)
    @link      https://pxts.ch
 */
class Phlesk
{
    const VERSION = '0.1';

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
        $tolerant = false,
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

        @param Bool  $primaryOnly   Only return domains that are primary domains for a
                                    subscription.
        @param Bool  $hosting       Only return domains that have hosting enabled.
        @param Bool  $mail          Only return domains that have mail service enabled.
        @param Array $filterMethods An optional function to apply as a filter.

        @return Array Returns a list of \pm_Domain objects.
     */
    public static function getAllDomains(
        $primaryOnly = false,
        $hosting = false,
        $mail = false,
        $filterMethods = []
    ) {
        $client = null;
        $domains = [];
        $result = [];

        $module = \Phlesk\Context::getModuleId();
        $extension = ucfirst(strtolower($module));

        $filterClass = "Modules_{$extension}_Utils";

        $session = \pm_Session::isExist();

        if ($session) {
            $client = \pm_Session::getClient();
        }

        if ($client == null) {
            $domains = \pm_Domain::getAllDomains($primaryOnly);
        } elseif ($client->isAdmin()) {
            $domains = \pm_Domain::getAllDomains($primaryOnly);
        } elseif ($client->isReseller()) {
            $all_domains = \pm_Domain::getAllDomains($primaryOnly);

            foreach ($all_domains as $domain) {
                if ($client->hasAccessToDomain($domain->getId())) {
                    $domains[] = $domain;
                }
            }
        } else {
            $domains = \pm_Domain::getDomainsByClient($client, $primaryOnly);
        }

        foreach ($domains as $domain) {
            if ($hosting && !\Phlesk\Domain::hasHosting($domain)) {
                continue;
            }

            if ($mail && !\Phlesk\Domain::hasMailService($domain)) {
                continue;
            }

            $skip = false;

            if ($filterMethods) {
                foreach ($filterMethods as $filterMethod) {
                    if (method_exists($filterClass, $filterMethod)) {
                        $method = "{$filterClass}::{$filterMethod}";

                        $result = call_user_func_array($method, [$domain]);

                        if (!$result) {
                            $skip = true;
                        }
                    }
                }
            }

            if ($skip) {
                continue;
            }

            $result[] = $domain;
        }

        return $result;
    }

    /**
        Get a \pm_Domain using its GUID.

        @param String $domain_guid The GUID of the domain to find and return.

        @return \pm_Domain|NULL
     */
    public static function getDomainByGuid($domain_guid)
    {
        // Must use \pm_Domain to avoid loops
        $domains = \pm_Domain::getAllDomains();

        foreach ($domains as $domain) {
            if ($domain->getGuid() == $domain_guid) {
                return $domain;
            }
        }

        return null;
    }

    /**
        Get a \pm_Domain by its numeric identifier.  Really, you could just use:

        ```php
           $domain = new \pm_Domain($domain_id);
        ```

        However, using an ID for a non-existent domain will throw an exception.

        @param Int $domain_id The ID of the domain to return.

        @return \pm_Domain|NULL
     */
    public static function getDomainById($domain_id)
    {
        // Must use \pm_Domain to avoid loops
        $domains = \pm_Domain::getAllDomains();

        foreach ($domains as $domain) {
            if ($domain->getId() == $domain_id) {
                return $domain;
            }
        }

        return null;
    }

    /**
        Get a \pm_Domain by its name.

        @param String $domain_name The name of the domain to return.

        @return \pm_Domain|NULL
     */
    public static function getDomainByName($domain_name)
    {
        // Must use \pm_Domain to avoid loops
        $domains = \pm_Domain::getAllDomains();

        foreach ($domains as $domain) {
            if ($domain->getName() == $domain_name) {
                return $domain;
            }
        }

        return null;
    }

    /**
        Get a name for a \pm_Domain by its ID.

        @param Int $domain_id The ID for the domain to obtain the name for.

        @return String
     */
    public static function getDomainNameByID($domain_id)
    {
        $domain = \Phlesk::getDomainById($domain_id);
        return $domain->getName();
    }

    /**
        Get the primary domain for any domain.

        Basically, maybe, the primary subscription domain. Terminology is confusing.

        @param String $domain_guid The GUID for the domain to retain the primary domain for.

        @return \pm_Domain|NULL
     */
    public static function getPrimaryDomain($domain_guid)
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
    public static function isPrimaryDomain($domain_guid)
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
