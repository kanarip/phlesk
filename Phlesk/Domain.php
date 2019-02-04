<?php
    namespace Phlesk;

    class Domain extends \pm_Domain {
        public static function getAllDomains($mainDomainsOnly = FALSE) {
            pm_Log::err("Use Phlesk::getAllDomains()");

            return \Phlesk::getAllDomains($mainDomainsOnly);
        }

        public static function getByGuid(String $domain_guid) {
            pm_Log::err("Use Phlesk::getDomainByGuid()");

            return \Phlesk::getDomainByGuid($domain_guid);
        }

        public function hasHosting() {
            \pm_Log::debug("Phlesk_Domain->hasHosting() for {$this->getName()} in " . __FILE__);

            return parent::hasHosting();
        }
    }
