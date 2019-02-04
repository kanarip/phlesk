<?php
    class Phlesk {
        static function getDomainByGuid($domain_guid) {
            $domains = \Phlesk::getAllDomains();

            foreach ($domains as $domain) {
                if ($domain->getGuid() == $domain_guid) {
                    return new \Phlesk\Domain($domain->getId());
                }
            }

            return NULL;
        }


        public static function getAllDomains($mainDomainsOnly = FALSE) {
            $domains = Array();

            $pm_domains = \pm_Domain::getAllDomains($mainDomainsOnly);

            foreach ($pm_domains as $pm_domain) {
                $domains[] = new \Phlesk\Domain($pm_domain->getId());
            }

            return $domains;
        }
    }
