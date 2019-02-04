<?php
    namespace Phlesk;

    class Domain extends \pm_Domain {
        public static function getAllDomains($mainDomainsOnly = FALSE) {
            \pm_Log::err("Use Phlesk::getAllDomains()");

            return \Phlesk::getAllDomains($mainDomainsOnly);
        }

        /**
            - param should be string, not enforced by pm_domain::getByGuid
        */
        public static function getByGuid($domain_guid) {
            \pm_Log::err("Use Phlesk::getDomainByGuid()");

            return \Phlesk::getDomainByGuid((String)$domain_guid);
        }

        public function hasHosting() {
            \pm_Log::debug("Phlesk_Domain->hasHosting() for {$this->getName()} in " . __FILE__);

            return parent::hasHosting();
        }
    }
