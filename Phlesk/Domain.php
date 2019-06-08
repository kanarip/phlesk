<?php
    /**
        Extending \pm_Domain.
    */
    namespace Phlesk;

    /**
        Extending \pm_Domain
    */
    class Domain extends \pm_Domain {

        /**
            Retrieve a list of all domains.

            Do not use this function. Instead use \Phlesk::getAllDomains();

            @param Boolean $main        Main domains only.
            @param Boolean $hosting     Domains with hosting only.
            @param Boolean $mail        Domains with mail service only.

            @return Array
        */
        public static function getAllDomains($main = FALSE, $hosting = FALSE, $mail = FALSE) {
            \pm_Log::warn("Use Phlesk::getAllDomains()");

            return \Phlesk::getAllDomains($main, $hosting, $mail);
        }

        /**
            Override \pm_Domain::getByGuid().

            Do not use this function. Instead use \Phlesk::getDomainByGuid();

            Needed because \pm_Domain::getGuid() will happily log an error rather than simply
            return NULL.

            @param String $domain_guid  The GUID.
            @return \Phlesk\Domain|NULL
        */
        public static function getByGuid(String $domain_guid) {
            \pm_Log::warn("Use Phlesk::getDomainByGuid()");

            return \Phlesk::getDomainByGuid((String)$domain_guid);
        }

        /**
            Override \pm_Domain::getByDomainId().

            Do not use this function. Instead use \Phlesk::getDomainById();

            Needed because \pm_Domain::getByDomainId() will happily log an error rather than simply
            return NULL.

            @param Integer $domain_id  The ID.
            @return \Phlesk\Domain|NULL
        */
        public static function getByDomainId($domain_id) {
            \pm_Log::warn("Use Phlesk::getDomainById()");

            return \Phlesk::getDomainById((Integer)$domain_id);
        }

        /**
            Determine if a domain actually has hosting.

            Needed because \pm_Domain::hasHosting() does not accurately reflect the then-current
            status.

            @return Boolean
        */
        public function hasHosting() {
            \pm_Log::debug("Phlesk_Domain->hasHosting() for {$this->getName()} in " . __FILE__);

            return parent::hasHosting();
        }


        /**
            Determine if a domain actually has mail service enabled.

            Needed because the function doesn't exist for \pm_Domain.

            @return Boolean
        */
        public function hasMailService() {
            \pm_Log::err("Not yet implemented: Phlesk\Domain->hasMailService()");
            return TRUE;
        }
    }
