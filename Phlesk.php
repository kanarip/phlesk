<?php
    class Phlesk {
        public static function contextIn($target) {
            return \Phlesk\Context::in($target);
        }

        public statuc function contextOut($target, $return = NULL) {
            return \Phlesk\Context::out($target, $return);
        }

        public static function getDomainByGuid($domain_guid) {
            $domains = \Phlesk::getAllDomains();

            foreach ($domains as $domain) {
                if ($domain->getGuid() == $domain_guid) {
                    return new \Phlesk\Domain($domain->getId());
                }
            }

            return NULL;
        }

        /**
            Get a domain by its numeric identifier.  Really, you could just use:

                $domain = new\Phlesk\Domain($domain_id);
        */
        public static function getDomainById($domain_id) {
            $domain = new \Phlesk\Domain($domain_id);

            return $domain;
        }

        public static function getDomainByName($domain_name) {
            $pm_domain = \pm_Domain::getByName($domain_name);

            $domain = \Phlesk\Domain($pm_domain->getId());

            return $domain;
        }

        /**

            @param $main    Only return domains that are primary domains for a subscription.
            @param $hosting Only return domains that have hosting enabled.
            @param $mail    Only return domains that have mail service enabled.
        */
        public static function getAllDomains($main = FALSE, $hosting = FALSE, $mail = FALSE) {
            $domains = Array();

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
    }
