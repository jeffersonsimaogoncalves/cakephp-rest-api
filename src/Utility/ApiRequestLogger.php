<?php

namespace RestApi\Utility;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\Exception\RolledbackTransactionException;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * Class ApiRequestLogger
 *
 * Handles the request logging.
 *
 * @package RestApi\Utility
 */
class ApiRequestLogger
{

    /**
     * Logs the request and response data into database.
     *
     * @param  \Cake\Http\ServerRequest  $request  The \Cake\Network\Request object
     * @param  \Cake\Http\Response  $response  The \Cake\Http\Response object
     */
    public static function log(ServerRequest $request, Response $response)
    {
        if (!self::_tableExists()) {
            return;
        }

        Configure::write('requestLogged', true);

        try {
            $apiRequests = TableRegistry::getTableLocator()->get('RestApi.ApiRequests');
            $entityData = [
                'http_method' => $request->getMethod(),
                'endpoint' => $request->getRequestTarget(),
                'token' => Configure::read('accessToken'),
                'ip_address' => $request->clientIp(),
                'request_data' => json_encode($request->getData()),
                'response_code' => $response->getStatusCode(),
                'response_data' => $response->getBody(),
                'exception' => Configure::read('apiExceptionMessage'),
            ];
            $entity = $apiRequests->newEntity($entityData);
            $apiRequests->save($entity);
        } catch (RolledbackTransactionException $e) {
            return;
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @return bool
     */
    protected static function _tableExists(): bool
    {
        $db = ConnectionManager::get('default');
        $tables = $db->getSchemaCollection()->listTables();

        if (in_array('api_requests', $tables)) {
            return true;
        }

        return false;
    }
}
