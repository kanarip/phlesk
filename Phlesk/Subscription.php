<?php
/**
    Shortcut to addressing "subscriptions".

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
    Shortcut to addressing "subscriptions".

    PHP Version 5

    @category  PHP
    @package   Phlesk
    @author    Jeroen van Meeuwen (Kolab Systems) <vanmeeuwen@kolabsys.com>
    @author    Christian Mollekopf (Kolab Systems) <mollekopf@kolabsys.com>
    @copyright 2019 Kolab Systems AG <contact@kolabsystems.com>
    @license   GPLv3 (https://www.gnu.org/licenses/gpl.txt)
    @link      https://pxts.ch
 */
class Subscription
{
    /**
        Obtain the (primary) domains for a subscription, by referencing one of the subscription's
        domains.

        @param \Phlesk\Domain $domain   The Phlesk domain from which to go up, then back down.
        @param Bool           $mainOnly Whether or not to include sub-domains and alias domains.

        @return Array A list containing \Phlesk\Domain items for the subscription.
     */
    public static function getDomains(\Phlesk\Domain $domain, $mainOnly = true)
    {
        // No way up to the subscription, go through client
        $client = $domain->getClient();
        $homepath = $domain->getHomePath();

        $subscription_domains = [];
        $domains = \pm_Domain::getDomainsByClient($client);

        foreach ($domains as $domain) {
            if ($domain->getHomePath() == $homepath) {
                $subscription_domains[] = $domain;
            }
        }

        return $subscription_domains;
    }
}
