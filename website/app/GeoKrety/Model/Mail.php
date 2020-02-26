<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class Mail extends Base {
    use \Validation\Traits\CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk-mail';

    protected $fieldConf = [
        'token' => [
            'type' => Schema::DT_VARCHAR128,
            'validate' => 'not_empty',
        ],
        'from' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'to' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
        ],
        'subject' => [
            'type' => Schema::DT_VARCHAR128,
            'filter' => 'trim|HTMLPurifier',
            'validate' => 'not_empty|max_len,255',
        ],
        'content' => [
            'type' => Schema::DT_TEXT,
            'filter' => 'trim|HTMLPurifier',
            'validate' => 'not_empty',
        ],
        'sent_on_datetime' => [
             'type' => Schema::DT_DATETIME,
        ],
        'ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
    ];

    public function get_sent_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            // generate random Token
            $seed = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
            shuffle($seed);
            $rand = '';
            foreach (array_rand($seed, GK_SITE_MAIL_TOKEN_LENGTH) as $k) {
                $rand .= $seed[$k];
            }
            $self->token = $rand;
            $self->ip = \Base::instance()->get('IP');
        });
    }
}
