<?php

namespace RestApi\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\Table;

/**
 * Class ApiRequestsTable
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 *
 * @package RestApi\Model\Table
 */
class ApiRequestsTable extends Table
{

    /**
     * set connection name
     *
     * @return string
     */
    public static function defaultConnectionName()
    {
        $connection = Configure::read('RestApi.connection');
        if (!empty($connection)) {
            return $connection;
        };

        return parent::defaultConnectionName();
    }

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     *
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('api_requests');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }
}
