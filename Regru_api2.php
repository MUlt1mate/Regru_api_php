<?php
/**
 * @author: MUlt1mate
 * Date: 29.05.13
 * Time: 17:57
 * PHP класс для работы с REG.RU API 2.0
 * https://www.reg.ru/support/help/API-version2
 */

class Regru_api2
{
    const API_URL = 'https://api.reg.ru/api/regru2/';

    //Общие коды ошибок
    const ERROR_NO_USERNAME = 'NO_USERNAME';
    const ERROR_NO_AUTH = 'NO_AUTH';
    const ERROR_PASSWORD_AUTH_FAILED = 'PASSWORD_AUTH_FAILED';
    const ERROR_RESELLER_AUTH_FAILED = 'RESELLER_AUTH_FAILED';
    const ERROR_ACCESS_DENIED = 'ACCESS_DENIED';
    const ERROR_PURCHASES_DISABLED = 'PURCHASES_DISABLED';
    //Ошибки идентификации доменов, сервисов, папок
    const ERROR_DOMAIN_NOT_FOUND = 'DOMAIN_NOT_FOUND';
    const ERROR_SERVICE_NOT_FOUND = 'SERVICE_NOT_FOUND';
    const ERROR_SERVICE_NOT_SPECIFIED = 'SERVICE_NOT_SPECIFIED';
    const ERROR_SERVICE_ID_NOT_FOUND = 'SERVICE_ID_NOT_FOUND';
    const ERROR_NO_DOMAIN = 'NO_DOMAIN';
    const ERROR_INVALID_DOMAIN_NAME_FORMAT = 'INVALID_DOMAIN_NAME_FORMAT';
    const ERROR_INVALID_SERVICE_ID = 'INVALID_SERVICE_ID';
    const ERROR_INVALID_DOMAIN_NAME_PUNYCODE = 'INVALID_DOMAIN_NAME_PUNYCODE';
    const ERROR_BAD_USER_SERVID = 'BAD_USER_SERVID';
    const ERROR_USER_SERVID_IS_NOT_UNIQUE = 'USER_SERVID_IS_NOT_UNIQUE';
    const ERROR_TOO_MANY_OBJECTS_IN_ONE_REQUEST = 'TOO_MANY_OBJECTS_IN_ONE_REQUEST';
    //Ошибки доступности
    const ERROR_DOMAIN_BAD_NAME = 'DOMAIN_BAD_NAME';
    const ERROR_DOMAIN_BAD_NAME_ONLYDIGITS = 'DOMAIN_BAD_NAME_ONLYDIGITS';
    const ERROR_HAVE_MIXED_CODETABLES = 'HAVE_MIXED_CODETABLES';
    const ERROR_DOMAIN_BAD_TLD = 'DOMAIN_BAD_TLD';
    const ERROR_TLD_DISABLED = 'TLD_DISABLED';
    const ERROR_DOMAIN_NAME_MUSTBEENG = 'DOMAIN_NAME_MUSTBEENG';
    const ERROR_DOMAIN_NAME_MUSTBERUS = 'DOMAIN_NAME_MUSTBERUS';
    const ERROR_DOMAIN_ALREADY_EXISTS = 'DOMAIN_ALREADY_EXISTS';
    const ERROR_DOMAIN_INVALID_LENGTH = 'DOMAIN_INVALID_LENGTH';
    const ERROR_DOMAIN_STOP_LIST = 'DOMAIN_STOP_LIST';
    const ERROR_DOMAIN_STOP_PATTERN = 'DOMAIN_STOP_PATTERN';
    const ERROR_FREE_DATE_IN_FUTURE = 'FREE_DATE_IN_FUTURE';
    const ERROR_NO_DOMAINS_CHECKED = 'NO_DOMAINS_CHECKED';
    const ERROR_NO_CONTRACT = 'NO_CONTRACT';
    const ERROR_INVALID_PUNYCODE_INPUT = 'INVALID_PUNYCODE_INPUT';
    const ERROR_CONNECTION_FAILED = 'CONNECTION_FAILED';
    const ERROR_DOMAIN_ALREADY_ORDERED = 'DOMAIN_ALREADY_ORDERED';
    const ERROR_DOMAIN_EXPIRED = 'DOMAIN_EXPIRED';
    const ERROR_DOMAIN_TOO_YOUNG = 'DOMAIN_TOO_YOUNG';
    const ERROR_CANT_OBTAIN_EXPDATE = 'CANT_OBTAIN_EXPDATE';
    const ERROR_DOMAIN_CLIENT_TRANSFER_PROHIBITED = 'DOMAIN_CLIENT_TRANSFER_PROHIBITED';
    const ERROR_DOMAIN_TRANSFER_PROHIBITED_UNKNOWN = 'DOMAIN_TRANSFER_PROHIBITED_UNKNOWN';
    const ERROR_DOMAIN_REGISTERED_VIA_DIRECTI = 'DOMAIN_REGISTERED_VIA_DIRECTI';
    const ERROR_NOT_FOUND_UNIQUE_REQUIRED_DATA = 'NOT_FOUND_UNIQUE_REQUIRED_DATA';
    const ERROR_ORDER_ALREADY_PAYED = 'ORDER_ALREADY_PAYED';
    const ERROR_DOUBLE_ORDER = 'DOUBLE_ORDER';
    const ERROR_DOMAIN_ORDER_LOCKED = 'DOMAIN_ORDER_LOCKED';
    const ERROR_UNAVAILABLE_DOMAIN_ZONE = 'UNAVAILABLE_DOMAIN_ZONE';
    //Ошибки при работе с DNS-зонами
    const ERROR_DOMAIN_IS_NOT_USE_REGRU_NSS = 'DOMAIN_IS_NOT_USE_REGRU_NSS';
    const ERROR_REVERSE_ZONE_API_NOT_SUPPORTED = 'REVERSE_ZONE_API_NOT_SUPPORTED';
    const ERROR_ZONES_VARY = 'ZONES_VARY';
    const ERROR_IP_INVALID = 'IP_INVALID';
    const ERROR_SUBD_INVALID = 'SUBD_INVALID';
    const ERROR_CONFLICT_CNAME = 'CONFLICT_CNAME';
    //Другие ошибки
    const ERROR_NO_SUCH_COMMAND = 'NO_SUCH_COMMAND';
    const ERROR_HTTPS_ONLY = 'HTTPS_ONLY';
    const ERROR_PARAMETER_MISSING = 'PARAMETER_MISSING';
    const ERROR_PARAMETER_INCORRECT = 'PARAMETER_INCORRECT';
    const ERROR_NOT_ENOUGH_MONEY = 'NOT_ENOUGH_MONEY';
    const ERROR_INTERNAL_ERROR = 'INTERNAL_ERROR';
    const ERROR_SERVICE_OPERATIONS_DISABLED = 'SERVICE_OPERATIONS_DISABLED';
    const ERROR_UNSUPPORTED_CURRENCY = 'UNSUPPORTED_CURRENCY';

    private $username = 'test';
    private $password = 'test';

    private $errors = array();
    /**
     * @var object
     */
    private $logger;

    private $parameters = array(
        'io_encoding' => 'utf8',
        'input_format' => 'plain',
        'output_format' => 'json',
        'lang' => 'ru'
    );

    /**
     * Оправляет POST запрос на указанный URL и возвращает ответ
     * @param string $url
     * @param array $data
     * @return string
     */
    private function sendHttpData($url, $data)
    {
        $query = http_build_query($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_POST, 1);

        $content = curl_exec($ch);
        $error = curl_error($ch);
        if (!empty($error)) {
            $this->log($error);
        }
        curl_close($ch);
        return $content;
    }

    /**
     * Формирует запрос к API
     * @param string $method
     * @param string $category
     * @param array $data
     * @return array|bool
     */
    private function send_query($method, $category = null, $data = null)
    {
        $this->errors = array();
        $url = self::API_URL;
        $post = (empty($data)) ? $this->parameters : array_merge($data, $this->parameters);
        if (!empty($category))
            $url .= $category . '/';
        $url .= $method . '?username=' . $this->username . '&password=' . $this->password;
        $this->log('request: ' . print_r($url, true) . "\n" . print_r($data, true));
        $result = $this->sendHttpData($url, $post);
        $this->log('response: ' . print_r($result, true));
        if (empty($result)) {
            $this->errors[] = 'Неизвестная ошибка';
            return false;
        }
        $answer = json_decode($result, true);
        if (isset($answer['error_params']['error_detail']))
            foreach ($answer['error_params']['error_detail'] as $error) {
                $this->errors[] = $error['error_text'];
            }
        if ('error' == $answer['result']) {
            $this->errors[$answer['error_code']] = $answer['error_text'];
            return false;
        }
        return $answer['answer'];
    }

    /**
     * Возращает массив ошибок, которые возвращает API
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * для тестирования, получение логина и идентификатора залогиненого пользователя
     * @link https://www.reg.ru/support/help/API-version2#nop
     * @return array|bool
     */
    public function nop()
    {
        return $this->send_query('nop');
    }

    /**
     * удалить ресурсную запись
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#zone_remove_record
     * @param string $domain_name
     * @param string $subdomain
     * @param string $record_type
     * @param string $content
     * @param int $priority
     * @return array|bool
     */
    public function remove_record($domain_name, $subdomain, $record_type, $content, $priority = 0)
    {
        $data = array(
            'domain_name' => $domain_name,
            'subdomain' => $subdomain,
            'record_type' => $record_type,
            'content' => $content,
        );
        if ('MX' == $record_type)
            $data['priority'] = $priority;
        return $this->send_query('remove_record', 'zone', $data);
    }

    /**
     * получение ресурсных записей зоны
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#zone_get_resource_records
     * @param string $domain_name
     * @return array|bool
     */
    public function get_records($domain_name)
    {
        $result = $this->send_query('get_resource_records', 'zone', array(
            'domain_name' => $domain_name,
        ));
        if (is_array($result))
            return $result['domains'][0]['rrs'];
        return $result;
    }

    /**
     * добавить запись ns
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#zone_add_ns
     * @param string $domain_name
     * @param string $subdomain
     * @param string $dns_server
     * @param int $record_number
     * @return array|bool
     */
    public function add_ns($domain_name, $subdomain, $dns_server, $record_number)
    {
        return $this->send_query('add_ns', 'zone', array(
            'domain_name' => $domain_name,
            'subdomain' => $subdomain,
            'dns_server' => $dns_server,
            'record_number' => $record_number,
        ));
    }

    /**
     * cвязать поддомен с IP-адресом
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#zone_add_alias
     * @param string $domain_name
     * @param string $subdomain
     * @param string $ip
     * @return array|bool
     */
    public function add_alias($domain_name, $subdomain, $ip)
    {
        return $this->send_query('add_alias', 'zone', array(
            'domain_name' => $domain_name,
            'subdomain' => $subdomain,
            'ipaddr' => $ip,
        ));
    }

    /**
     * cвязать поддомен с адресом другого домена
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#zone_add_cname
     * @param string $domain_name
     * @param string $subdomain
     * @param string $canonical_name
     * @return array|bool
     */
    public function add_cname($domain_name, $subdomain, $canonical_name)
    {
        return $this->send_query('add_cname', 'zone', array(
            'domain_name' => $domain_name,
            'subdomain' => $subdomain,
            'canonical_name' => $canonical_name,
        ));
    }

    /**
     * назначить обслуживающий почтовый сервер
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#zone_add_mx
     * @param string $domain_name
     * @param string $subdomain
     * @param string $priority
     * @param string $mail_server
     * @return array|bool
     */
    public function add_mx($domain_name, $subdomain, $priority, $mail_server)
    {
        return $this->send_query('add_mx', 'zone', array(
            'domain_name' => $domain_name,
            'subdomain' => $subdomain,
            'priority' => $priority,
            'mail_server' => $mail_server,
        ));
    }


    /**
     * заявка на регистрацию домена
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#domain_create
     * @param array $data
     * @return array|bool
     */
    public function registerDomain($data)
    {
        $data['enduser_ip'] = $_SERVER['REMOTE_ADDR'];
        return $this->send_query('create', 'domain', $data);
    }

    /**
     * получение списка DNS для доменов
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#domain_get_nss
     * @param string $domain_name
     * @return array|bool
     */
    public function getNSS($domain_name)
    {
        $result = $this->send_query('get_nss', 'domain', array(
            'domain_name' => $domain_name,
        ));
        if (is_array($result))
            return $result['domains'][0]['nss'];
        return $result;
    }

    /**
     * изменение DNS серверов домена
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#domain_update_nss
     * @param string $domain
     * @param string $ns0
     * @param string $ns1
     * @param string $ns2
     * @param string $ns3
     * @param string $ns0ip
     * @param string $ns1ip
     * @param string $ns2ip
     * @param string $ns3ip
     * @return array|bool
     */
    public function updateNss($domain, $ns0, $ns1, $ns2, $ns3, $ns0ip, $ns1ip, $ns2ip, $ns3ip)
    {
        return $this->send_query('update_nss', 'domain', array(
            'domain_name' => $domain,
            'ns0' => $ns0,
            'ns1' => $ns1,
            'ns2' => $ns2,
            'ns3' => $ns3,
            'ns0ip' => $ns0ip,
            'ns1ip' => $ns1ip,
            'ns2ip' => $ns2ip,
            'ns3ip' => $ns3ip,
        ));
    }

    /**
     * изменение контактных данных домена
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#domain_update_contacts
     * @param array $data
     * @return array|bool
     */
    public function updateContacts($data)
    {
        return $this->send_query('update_contacts', 'domain', $data);
    }

    /**
     * получение общей информации по услуге\домену
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#service_get_details
     * @param string $domain_name
     * @return array|bool
     */
    public function getContacts($domain_name)
    {
        $result = $this->send_query('get_details', 'service', array(
            'domain_name' => $domain_name,
        ));
        if (isset($result['services'][0]['details']))
            return $result['services'][0]['details'];
        return $result;
    }

    /**
     * продление домена или услуги
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#service_renew
     * @param string $domain_name
     * @param int $period
     * @return array|bool
     */
    public function renewDomain($domain_name, $period)
    {
        return $this->send_query('renew', 'service', array(
            'domain_name' => $domain_name,
            'period' => $period,
        ));
    }

    /**
     * изменение флага скрытия/отображения контактных данных в whois
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#domain_update_private_person_flag
     * @param string $domain_name
     * @param int $flag
     * @return array|bool
     */
    public function updatePrivatePerson($domain_name, $flag)
    {
        return $this->send_query('update_private_person_flag', 'domain', array(
            'domain_name' => $domain_name,
            'private_person_flag' => $flag,
        ));
    }

    /**
     * получить цены на все доменные зоны
     * @link https://www.reg.ru/support/help/API-version2?group_sidebar=partners_submenu#domain_get_prices
     * @return array|bool
     */
    public function get_price()
    {
        return $this->send_query('get_prices', 'domain');
    }

    /**
     * Логгирование сообщений
     * @param string $message
     */
    private function log($message)
    {
        if (is_object($this->logger))
            $this->logger->log($message);
    }

    /**
     * Установка объекта логгера
     * @param object $logger
     * @return bool
     */
    public function set_logger($logger)
    {
        if (method_exists($logger, 'log')) {
            $this->logger = $logger;
            return true;
        }
        return false;
    }

}