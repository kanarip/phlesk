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

namespace Phlesk;

/**
    Main class. Static utility functions mostly.

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

        @param String $command   The base command to execute.
        @param Array  $arguments The parameters to the command to execute.
        @param Bool   $tolerant  Whether or not the failure of execution is fatal (default).

        @return Array Result of command execution, including 'code', 'stderr', 'stdout'.
     */
    public static function exec(
        String $command,
        Array $arguments = [],
        Bool $tolerant = false
    ) {

        $result = \pm_ApiCli::callSbin($command, $arguments, \pm_ApiCli::RESULT_FULL);

        if ($result['code'] != 0 && !$tolerant) {
            pm_Log::err(
                "Error executing: '" . $command . " " . implode(' ', $arguments) . "'"
            );

            pm_Log::err(
                "stderr: " . $result['stderr']
            );
        }

        return $result;
    }

    /**
        Obtain a list of domains.

        @param Bool $main    Only return domains that are primary domains for a subscription.
        @param Bool $hosting Only return domains that have hosting enabled.
        @param Bool $mail    Only return domains that have mail service enabled.

        @return Array Returns a list of \Phlesk\Domain objects.
     */
    public static function getAllDomains($main = false, $hosting = false, $mail = false)
    {
        $domains = array();

        $pm_domains = \pm_Domain::getAllDomains($main);

        foreach ($pm_domains as $pm_domain) {
            $domain = new \Phlesk\Domain($pm_domain->getId());

            if ($hosting && !$domain->hasHosting()) {
                continue;
            }

            if ($mail && !$domain->hasMailService()) {
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
        $domains = \Phlesk::getAllDomains();

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
    public static function getDomainById(Integer $domain_id)
    {
        $domain = new \Phlesk\Domain($domain_id);

        return $domain;
    }

    /**
        Get a \Phlesk\Domain by its name.

        @param String $domain_name The name of the domain to return.

        @return \Phlesk\Domain|NULL
     */
    public static function getDomainByName($domain_name)
    {
        $pm_domain = \pm_Domain::getByName($domain_name);

        $domain = new \Phlesk\Domain($pm_domain->getId());

        return $domain;
    }

    /**
        Get domains for a client.

        @param \pm_Client $client   The pm_Client to return domains for.
        @param Bool       $mainOnly Only return main domains, not sub-domains, nor aliases.

        @return Array A list with \Phlesk\Domain items.
     */
    public static function getDomainsByClient(\pm_Client $client, $mainOnly = false)
    {
        return \pm_Domain::getDomainsByClient($client, $mainOnly);
    }
}
